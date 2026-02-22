<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('admin');

$stats = [];
$stats['users'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$stats['sellers'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
$stats['products'] = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$stats['orders'] = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$stats['revenue'] = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('paid','shipped','delivered')")->fetchColumn();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Admin Dashboard</h1>
    <div>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('admin/sellers.php')); ?>">Manage Sellers</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('admin/categories.php')); ?>">Categories</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('admin/orders.php')); ?>">Orders</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('admin/users.php')); ?>">Users</a>
    </div>
</div>
<div class="row g-3">
    <?php foreach ($stats as $label => $value): ?>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="card-title text-capitalize"><?= e($label); ?></h5>
                    <p class="display-6 mb-0"><?= $label === 'revenue' ? '$' . number_format($value, 2) : e((string) $value); ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
