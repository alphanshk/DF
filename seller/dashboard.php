<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('seller');
$user = current_user();
$sellerId = (int)$user['id'];

$stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE seller_id=?');
$stmt->execute([$sellerId]);
$productCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.seller_id=?");
$stmt->execute([$sellerId]);
$orderCount = (int)$stmt->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>
<h3>Seller Dashboard</h3>
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Products</h6><h4><?= $productCount ?></h4></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Order Items Sold</h6><h4><?= $orderCount ?></h4></div></div></div>
</div>
<a class="btn btn-primary" href="/seller/products.php">Manage Products</a>
<a class="btn btn-outline-secondary" href="/seller/orders.php">View Orders</a>
<?php include __DIR__ . '/../includes/footer.php'; ?>
