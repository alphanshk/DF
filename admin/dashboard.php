<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$stats = [
    'users' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
    'sellers' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='seller'")->fetchColumn(),
    'products' => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'revenue' => (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders")->fetchColumn(),
];

include __DIR__ . '/../includes/header.php';
?>
<h3>Admin Dashboard</h3>
<div class="row g-3 mb-4">
<?php foreach ($stats as $label => $value): ?>
    <div class="col-md-3">
        <div class="card stat-card"><div class="card-body">
            <h6 class="text-muted text-capitalize"><?= e($label) ?></h6>
            <h4><?= e((string)$value) ?></h4>
        </div></div>
    </div>
<?php endforeach; ?>
</div>
<div class="list-group">
    <a class="list-group-item list-group-item-action" href="/admin/sellers.php">Approve / Block Sellers</a>
    <a class="list-group-item list-group-item-action" href="/admin/categories.php">Manage Categories</a>
    <a class="list-group-item list-group-item-action" href="/admin/orders.php">View All Orders</a>
    <a class="list-group-item list-group-item-action" href="/admin/users.php">Manage Users</a>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
