<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid request token.');
        redirect('/admin/categories.php');
    }
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id=?');
        $stmt->execute([(int)$_POST['delete_id']]);
        set_flash('success', 'Category deleted.');
    } else {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
            set_flash('success', 'Category added.');
        }
    }
    redirect('/admin/categories.php');
}
$categories = $pdo->query('SELECT * FROM categories ORDER BY id DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h3>Categories</h3>
<form method="post" class="row g-2 mb-3">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="col-md-8"><input type="text" name="name" class="form-control" placeholder="Category name" required></div>
    <div class="col-md-4"><button class="btn btn-success w-100">Add Category</button></div>
</form>
<table class="table table-striped">
<tr><th>ID</th><th>Name</th><th>Action</th></tr>
<?php foreach ($categories as $cat): ?>
<tr>
<td><?= (int)$cat['id'] ?></td><td><?= e($cat['name']) ?></td>
<td>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="delete_id" value="<?= (int)$cat['id'] ?>">
<button class="btn btn-sm btn-danger" onclick="return confirm('Delete category?')">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
