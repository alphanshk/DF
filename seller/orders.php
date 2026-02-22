<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('seller');

$sellerId = (int) $_SESSION['user']['id'];
$stmt = $pdo->prepare('SELECT o.id, u.name AS buyer, o.status, o.created_at, SUM(oi.quantity * oi.price) AS subtotal FROM order_items oi JOIN products p ON p.id=oi.product_id JOIN orders o ON o.id=oi.order_id JOIN users u ON u.id=o.user_id WHERE p.seller_id=:seller_id GROUP BY o.id, u.name, o.status, o.created_at ORDER BY o.created_at DESC');
$stmt->execute(['seller_id' => $sellerId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Orders for Your Products</h1>
<table class="table table-bordered table-striped">
<thead><tr><th>Order #</th><th>Buyer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
<tr>
<td><?= (int)$o['id']; ?></td>
<td><?= e($o['buyer']); ?></td>
<td>$<?= number_format((float)$o['subtotal'],2); ?></td>
<td><?= e($o['status']); ?></td>
<td><?= e($o['created_at']); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
