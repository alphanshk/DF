<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('seller');

$sellerId = (int) $_SESSION['user']['id'];
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    redirect('seller/products.php');
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id AND seller_id = :seller_id');
$stmt->execute(['id' => $id, 'seller_id' => $sellerId]);
$product = $stmt->fetch();
if (!$product) {
    set_flash('danger', 'Product not found.');
    redirect('seller/products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('seller/edit_product.php?id=' . $id);
    }

    $name = trim($_POST['name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);

    if ($name === '' || $price <= 0 || $stock < 0 || $categoryId <= 0) {
        set_flash('danger', 'Invalid product data.');
        redirect('seller/edit_product.php?id=' . $id);
    }

    $params = ['name' => $name, 'price' => $price, 'stock' => $stock, 'category_id' => $categoryId, 'id' => $id, 'seller_id' => $sellerId];
    $sql = 'UPDATE products SET name=:name, price=:price, stock=:stock, category_id=:category_id';

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed, true) || $_FILES['image']['size'] > 2 * 1024 * 1024) {
            set_flash('danger', 'Invalid image. Use JPG/PNG/WEBP up to 2MB.');
            redirect('seller/edit_product.php?id=' . $id);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('prod_', true) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
        $sql .= ', image=:image';
        $params['image'] = $imageName;
    }

    $sql .= ' WHERE id=:id AND seller_id=:seller_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    set_flash('success', 'Product updated.');
    redirect('seller/products.php');
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$pageTitle = 'Edit Product';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Edit Product</h1>
<form method="post" enctype="multipart/form-data" class="card p-3">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
    <input type="hidden" name="id" value="<?= (int) $product['id']; ?>">
    <div class="row g-2">
        <div class="col-md-6"><input class="form-control" name="name" value="<?= e($product['name']); ?>" required></div>
        <div class="col-md-2"><input class="form-control" type="number" step="0.01" min="0.01" name="price" value="<?= e((string)$product['price']); ?>" required></div>
        <div class="col-md-2"><input class="form-control" type="number" min="0" name="stock" value="<?= (int)$product['stock']; ?>" required></div>
        <div class="col-md-2"><select class="form-select" name="category_id" required><?php foreach($categories as $c): ?><option value="<?= (int)$c['id']; ?>" <?= (int)$c['id']===(int)$product['category_id']?'selected':''; ?>><?= e($c['name']); ?></option><?php endforeach; ?></select></div>
    </div>
    <div class="mt-2"><input type="file" class="form-control" name="image" accept="image/*"></div>
    <div class="mt-3"><button class="btn btn-primary">Save Changes</button></div>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
