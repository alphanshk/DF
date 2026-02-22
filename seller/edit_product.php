<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('seller');
$sellerId = (int)current_user()['id'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM products WHERE id=? AND seller_id=?');
$stmt->execute([$id, $sellerId]);
$product = $stmt->fetch();
if (!$product) {
    set_flash('danger', 'Product not found.');
    redirect('/seller/products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid token.');
        redirect('/seller/edit_product.php?id=' . $id);
    }
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    if ($name && $price > 0 && $stock >= 0 && $categoryId > 0) {
        $stmt = $pdo->prepare('UPDATE products SET name=?,price=?,stock=?,category_id=? WHERE id=? AND seller_id=?');
        $stmt->execute([$name, $price, $stock, $categoryId, $id, $sellerId]);
        set_flash('success', 'Product updated.');
        redirect('/seller/products.php');
    }
    set_flash('danger', 'Invalid input values.');
}

$categories = $pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h3>Edit Product</h3>
<form method="post" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
    <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= e($product['name']) ?>" required></div>
    <div class="col-md-3"><label class="form-label">Price</label><input type="number" step="0.01" name="price" class="form-control" value="<?= e((string)$product['price']) ?>" required></div>
    <div class="col-md-3"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" value="<?= (int)$product['stock'] ?>" required></div>
    <div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select" required>
        <?php foreach($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= (int)$product['category_id']===(int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
    </select></div>
    <div class="col-12"><button class="btn btn-primary">Save</button> <a href="/seller/products.php" class="btn btn-secondary">Cancel</a></div>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
