<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('seller');

$sellerId = (int) $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('seller/products.php');
    }

    $action = $_POST['action'] ?? 'add';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id AND seller_id = :seller_id');
        $stmt->execute(['id' => $id, 'seller_id' => $sellerId]);
        set_flash('success', 'Product deleted.');
        redirect('seller/products.php');
    }

    $name = trim($_POST['name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);

    if ($name === '' || $price <= 0 || $stock < 0 || $categoryId <= 0) {
        set_flash('danger', 'Invalid product data.');
        redirect('seller/products.php');
    }

    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed, true) || $_FILES['image']['size'] > 2 * 1024 * 1024) {
            set_flash('danger', 'Invalid image. Use JPG/PNG/WEBP up to 2MB.');
            redirect('seller/products.php');
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('prod_', true) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
    }

    if ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $sql = 'UPDATE products SET name=:name, price=:price, stock=:stock, category_id=:category_id';
        $params = ['name' => $name, 'price' => $price, 'stock' => $stock, 'category_id' => $categoryId, 'id' => $id, 'seller_id' => $sellerId];
        if ($imageName !== null) {
            $sql .= ', image=:image';
            $params['image'] = $imageName;
        }
        $sql .= ' WHERE id=:id AND seller_id=:seller_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        set_flash('success', 'Product updated.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO products (seller_id,name,price,stock,image,category_id) VALUES (:seller_id,:name,:price,:stock,:image,:category_id)');
        $stmt->execute(['seller_id' => $sellerId, 'name' => $name, 'price' => $price, 'stock' => $stock, 'image' => $imageName, 'category_id' => $categoryId]);
        set_flash('success', 'Product added.');
    }
    redirect('seller/products.php');
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.seller_id=:seller_id ORDER BY p.id DESC');
$stmt->execute(['seller_id' => $sellerId]);
$products = $stmt->fetchAll();

$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Manage Products</h1>
<div class="card mb-4"><div class="card-body">
<form method="post" enctype="multipart/form-data" class="row g-2">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
    <input type="hidden" name="action" value="add">
    <div class="col-md-3"><input class="form-control" name="name" placeholder="Product name" required></div>
    <div class="col-md-2"><input class="form-control" type="number" step="0.01" min="0.01" name="price" placeholder="Price" required></div>
    <div class="col-md-2"><input class="form-control" type="number" min="0" name="stock" placeholder="Stock" required></div>
    <div class="col-md-2"><select class="form-select" name="category_id" required><option value="">Category</option><?php foreach($categories as $c): ?><option value="<?= (int)$c['id']; ?>"><?= e($c['name']); ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><input class="form-control" type="file" name="image" accept="image/*"></div>
    <div class="col-md-1"><button class="btn btn-success w-100">Add</button></div>
</form></div></div>

<table class="table table-bordered table-striped">
<thead><tr><th>Name</th><th>Price</th><th>Stock</th><th>Category</th><th>Image</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($products as $p): ?>
<tr>
<td><?= e($p['name']); ?></td>
<td>$<?= number_format((float)$p['price'],2); ?></td>
<td><?= (int)$p['stock']; ?></td>
<td><?= e($p['category_name'] ?? 'Uncategorized'); ?></td>
<td><?php if($p['image']): ?><img src="<?= e(base_url('uploads/'.$p['image'])); ?>" width="60"><?php endif; ?></td>
<td>
<form method="post" class="d-inline">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id']; ?>">
<a class="btn btn-sm btn-warning" href="<?= e(base_url('seller/edit_product.php?id=' . (int)$p['id'])); ?>">Edit</a>
<button class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
