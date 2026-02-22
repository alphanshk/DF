<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('seller');
$sellerId = (int)current_user()['id'];

$sql = "SELECT o.id,o.status,o.created_at,u.name user_name,p.name product_name,oi.quantity,oi.price
        FROM order_items oi
        JOIN orders o ON oi.order_id=o.id
        JOIN products p ON oi.product_id=p.id
        JOIN users u ON o.user_id=u.id
        WHERE p.seller_id=?
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$sellerId]);
$items = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h3>My Orders</h3>
<table class="table table-striped table-sm">
<tr><th>Order#</th><th>Buyer</th><th>Product</th><th>Qty</th><th>Price</th><th>Status</th><th>Date</th></tr>
<?php foreach ($items as $item): ?>
<tr>
<td><?= (int)$item['id'] ?></td>
<td><?= e($item['user_name']) ?></td>
<td><?= e($item['product_name']) ?></td>
<td><?= (int)$item['quantity'] ?></td>
<td>$<?= number_format((float)$item['price'], 2) ?></td>
<td><?= e($item['status']) ?></td>
<td><?= e($item['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
