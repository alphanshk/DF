<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('user');

$userId = (int)current_user()['id'];
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h3>Order History</h3>
<?php foreach ($orders as $order): ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
        <span>Order #<?= (int)$order['id'] ?> (<?= e($order['status']) ?>)</span>
        <strong>$<?= number_format((float)$order['total_amount'],2) ?></strong>
    </div>
    <div class="card-body">
    <?php
    $itemStmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?');
    $itemStmt->execute([$order['id']]);
    $items = $itemStmt->fetchAll();
    ?>
    <ul class="mb-0">
    <?php foreach ($items as $item): ?>
        <li><?= e($item['name']) ?> x <?= (int)$item['quantity'] ?> - $<?= number_format((float)$item['price'],2) ?></li>
    <?php endforeach; ?>
    </ul>
    <small class="text-muted">Placed on <?= e($order['created_at']) ?></small>
    </div>
</div>
<?php endforeach; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
