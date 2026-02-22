<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('admin/categories.php');
    }

    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
        set_flash('success', 'Category created.');
    }
    redirect('admin/categories.php');
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Manage Categories</h1>
<form method="post" class="row g-2 mb-3">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
    <div class="col-md-6">
        <input type="text" class="form-control" name="name" placeholder="New category" required>
    </div>
    <div class="col-md-2">
        <button class="btn btn-success w-100">Add</button>
    </div>
</form>
<ul class="list-group">
    <?php foreach ($categories as $category): ?>
        <li class="list-group-item"><?= e($category['name']); ?></li>
    <?php endforeach; ?>
</ul>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
