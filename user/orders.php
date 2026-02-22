<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('user');

$stmt = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id=:user_id ORDER BY created_at DESC');
$stmt->execute(['user_id' => (int) $_SESSION['user']['id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'Order History';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Order History</h1>
<table class="table table-striped table-bordered">
<thead><tr><th>#</th><th>Total</th><th>Status</th><th>Tracking</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
<tr>
<td><?= (int)$o['id']; ?></td>
<td>$<?= number_format((float)$o['total_amount'],2); ?></td>
<td><?= e($o['status']); ?></td>
<td><?= e('Order #' . $o['id'] . ' - ' . ucfirst($o['status'])); ?></td>
<td><?= e($o['created_at']); ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
