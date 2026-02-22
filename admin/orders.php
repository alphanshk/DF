<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('admin');

$orders = $pdo->query('SELECT o.id, u.name AS user_name, o.total_amount, o.status, o.created_at FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC')->fetchAll();

$pageTitle = 'All Orders';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">All Orders</h1>
<table class="table table-striped table-bordered">
    <thead><tr><th>#</th><th>User</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= (int) $order['id']; ?></td>
                <td><?= e($order['user_name']); ?></td>
                <td>$<?= number_format((float) $order['total_amount'], 2); ?></td>
                <td><?= e($order['status']); ?></td>
                <td><?= e($order['created_at']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
