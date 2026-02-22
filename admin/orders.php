<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

$orders = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h3>All Orders</h3>
<table class="table table-bordered table-sm">
<tr><th>ID</th><th>User</th><th>Total</th><th>Status</th><th>Date</th></tr>
<?php foreach ($orders as $order): ?>
<tr>
    <td><?= (int)$order['id'] ?></td>
    <td><?= e($order['user_name']) ?></td>
    <td>$<?= number_format((float)$order['total_amount'], 2) ?></td>
    <td><?= e($order['status']) ?></td>
    <td><?= e($order['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
