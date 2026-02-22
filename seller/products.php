<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('seller');
$sellerId = (int)current_user()['id'];

function upload_image(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) return null;
    if ($file['size'] > 2 * 1024 * 1024) return null;
    $name = uniqid('prod_', true) . '.' . $allowed[$mime];
    $dest = __DIR__ . '/../uploads/' . $name;
    if (move_uploaded_file($file['tmp_name'], $dest)) return $name;
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid token.');
        redirect('/seller/products.php');
    }

    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id=? AND seller_id=?');
        $stmt->execute([(int)$_POST['delete_id'], $sellerId]);
        set_flash('success', 'Product deleted.');
        redirect('/seller/products.php');
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    if ($name === '' || $price <= 0 || $stock < 0 || $categoryId <= 0) {
        set_flash('danger', 'Invalid product data.');
        redirect('/seller/products.php');
    }

    $image = upload_image($_FILES['image'] ?? []);

    if ($id > 0) {
        if ($image) {
            $stmt = $pdo->prepare('UPDATE products SET name=?,price=?,stock=?,category_id=?,image=? WHERE id=? AND seller_id=?');
            $stmt->execute([$name, $price, $stock, $categoryId, $image, $id, $sellerId]);
        } else {
            $stmt = $pdo->prepare('UPDATE products SET name=?,price=?,stock=?,category_id=? WHERE id=? AND seller_id=?');
            $stmt->execute([$name, $price, $stock, $categoryId, $id, $sellerId]);
        }
        set_flash('success', 'Product updated.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO products (seller_id,name,price,stock,image,category_id) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$sellerId, $name, $price, $stock, $image, $categoryId]);
        set_flash('success', 'Product added.');
    }
    redirect('/seller/products.php');
}

$categories = $pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$stmt = $pdo->prepare('SELECT p.*, c.name category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.seller_id=? ORDER BY p.id DESC');
$stmt->execute([$sellerId]);
$products = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h3>Manage Products</h3>
<form method="post" enctype="multipart/form-data" class="row g-2 mb-4">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Product name" required></div>
<div class="col-md-2"><input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required></div>
<div class="col-md-2"><input type="number" name="stock" class="form-control" placeholder="Stock" required></div>
<div class="col-md-3">
<select name="category_id" class="form-select" required>
<option value="">Category</option>
<?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option><?php endforeach; ?>
</select>
</div>
<div class="col-md-2"><input type="file" name="image" class="form-control" accept="image/*"></div>
<div class="col-12"><button class="btn btn-success">Add Product</button></div>
</form>
<table class="table table-bordered table-sm">
<tr><th>Name</th><th>Price</th><th>Stock</th><th>Category</th><th>Image</th><th>Actions</th></tr>
<?php foreach ($products as $p): ?>
<tr>
<td><?= e($p['name']) ?></td><td>$<?= number_format((float)$p['price'],2) ?></td><td><?= (int)$p['stock'] ?></td><td><?= e($p['category_name'] ?? 'N/A') ?></td>
<td><?php if ($p['image']): ?><img src="/uploads/<?= e($p['image']) ?>" width="50"><?php endif; ?></td>
<td>
<a class="btn btn-warning btn-sm" href="/seller/edit_product.php?id=<?= (int)$p['id'] ?>">Edit</a>
<form method="post" class="d-inline">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= (int)$p['id'] ?>">
<button class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
