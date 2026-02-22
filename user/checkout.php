<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('user');

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    set_flash('warning', 'Your cart is empty.');
    redirect('user/cart.php');
}

$ids = implode(',', array_map('intval', array_keys($cart)));
$products = $pdo->query("SELECT id, name, price, stock FROM products WHERE id IN ({$ids})")->fetchAll();
$items = [];
$total = 0;
foreach ($products as $p) {
    $qty = min((int) ($cart[$p['id']] ?? 0), (int) $p['stock']);
    if ($qty <= 0) {
        continue;
    }
    $subtotal = $qty * (float) $p['price'];
    $items[] = ['product' => $p, 'quantity' => $qty, 'subtotal' => $subtotal];
    $total += $subtotal;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('user/checkout.php');
    }

    if (!$items) {
        set_flash('danger', 'Invalid cart state.');
        redirect('user/cart.php');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (:user_id, :total_amount, 'paid', NOW())");
        $stmt->execute(['user_id' => (int) $_SESSION['user']['id'], 'total_amount' => $total]);
        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)');
        $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :product_id AND stock >= :quantity');

        foreach ($items as $item) {
            $itemStmt->execute([
                'order_id' => $orderId,
                'product_id' => (int) $item['product']['id'],
                'quantity' => (int) $item['quantity'],
                'price' => (float) $item['product']['price'],
            ]);
            $stockStmt->execute(['quantity' => (int) $item['quantity'], 'product_id' => (int) $item['product']['id']]);
            if ($stockStmt->rowCount() === 0) {
                throw new RuntimeException('Stock update failed.');
            }
        }

        $pdo->commit();
        unset($_SESSION['cart']);
        set_flash('success', 'Order placed successfully.');
        redirect('user/orders.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        set_flash('danger', 'Checkout failed. Please try again.');
        redirect('user/cart.php');
    }
}

$pageTitle = 'Checkout';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Checkout</h1>
<table class="table table-bordered">
<thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
<td><?= e($item['product']['name']); ?></td>
<td><?= (int)$item['quantity']; ?></td>
<td>$<?= number_format((float)$item['product']['price'],2); ?></td>
<td>$<?= number_format((float)$item['subtotal'],2); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<h4>Total: $<?= number_format($total,2); ?></h4>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
    <button class="btn btn-success">Confirm Order</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
