<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('user');

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    set_flash('warning', 'Cart is empty.');
    redirect('/user/cart.php');
}

$ids = implode(',', array_map('intval', array_keys($cart)));
$products = $pdo->query("SELECT id,name,price,stock FROM products WHERE id IN ({$ids}) FOR UPDATE")->fetchAll();
if (!$products) {
    set_flash('danger', 'Cart products are unavailable.');
    redirect('/user/cart.php');
}

$total = 0;
$items = [];
foreach ($products as $p) {
    $qty = (int)($cart[$p['id']] ?? 0);
    if ($qty <= 0 || $qty > (int)$p['stock']) {
        set_flash('danger', 'Invalid quantity for ' . $p['name']);
        redirect('/user/cart.php');
    }
    $line = $qty * (float)$p['price'];
    $total += $line;
    $items[] = ['id' => $p['id'], 'qty' => $qty, 'price' => $p['price'], 'name' => $p['name']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid token.');
        redirect('/user/checkout.php');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO orders (user_id,total_amount,status,created_at) VALUES (?,?,?,NOW())');
        $stmt->execute([current_user()['id'], $total, 'processing']);
        $orderId = (int)$pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)');
        $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item['id'], $item['qty'], $item['price']]);
            $stockStmt->execute([$item['qty'], $item['id'], $item['qty']]);
            if ($stockStmt->rowCount() === 0) {
                throw new RuntimeException('Insufficient stock for ' . $item['name']);
            }
        }

        $pdo->commit();
        unset($_SESSION['cart']);
        set_flash('success', 'Order placed successfully. Order #' . $orderId);
        redirect('/user/orders.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        set_flash('danger', 'Checkout failed: ' . $e->getMessage());
        redirect('/user/cart.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h3>Checkout</h3>
<ul class="list-group mb-3">
<?php foreach ($items as $item): ?>
    <li class="list-group-item d-flex justify-content-between">
        <span><?= e($item['name']) ?> x <?= (int)$item['qty'] ?></span>
        <strong>$<?= number_format($item['qty'] * (float)$item['price'],2) ?></strong>
    </li>
<?php endforeach; ?>
<li class="list-group-item d-flex justify-content-between"><strong>Total</strong><strong>$<?= number_format($total,2) ?></strong></li>
</ul>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<button class="btn btn-success">Confirm Order</button>
<a href="/user/cart.php" class="btn btn-secondary">Back</a>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
