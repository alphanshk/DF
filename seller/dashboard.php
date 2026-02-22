<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('seller');

$sellerId = (int) $_SESSION['user']['id'];
$stats = [];
$stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE seller_id = :seller_id');
$stmt->execute(['seller_id' => $sellerId]);
$stats['products'] = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM order_items oi JOIN products p ON p.id=oi.product_id JOIN orders o ON o.id=oi.order_id WHERE p.seller_id=:seller_id');
$stmt->execute(['seller_id' => $sellerId]);
$stats['order_items'] = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COALESCE(SUM(oi.quantity*oi.price),0) FROM order_items oi JOIN products p ON p.id=oi.product_id JOIN orders o ON o.id=oi.order_id WHERE p.seller_id=:seller_id');
$stmt->execute(['seller_id' => $sellerId]);
$stats['revenue'] = (float) $stmt->fetchColumn();

$pageTitle = 'Seller Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3">Seller Dashboard</h1>
    <div>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('seller/products.php')); ?>">Manage Products</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('seller/orders.php')); ?>">View Orders</a>
    </div>
</div>
<div class="row g-3">
    <?php foreach ($stats as $label => $value): ?>
        <div class="col-md-4"><div class="card stat-card"><div class="card-body"><h5 class="text-capitalize"><?= e($label); ?></h5><p class="display-6 mb-0"><?= $label === 'revenue' ? '$' . number_format($value,2) : (int)$value; ?></p></div></div></div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
