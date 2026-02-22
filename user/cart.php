<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('user');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('user/cart.php');
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? 'update';

    if ($action === 'remove') {
        unset($_SESSION['cart'][$productId]);
    } else {
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $_SESSION['cart'][$productId] = $quantity;
    }

    set_flash('success', 'Cart updated.');
    redirect('user/cart.php');
}

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $rows = $pdo->query("SELECT id, name, price, stock, image FROM products WHERE id IN ({$ids})")->fetchAll();
    foreach ($rows as $row) {
        $qty = min($cart[$row['id']] ?? 1, (int) $row['stock']);
        $subtotal = $qty * (float) $row['price'];
        $total += $subtotal;
        $items[] = ['product' => $row, 'quantity' => $qty, 'subtotal' => $subtotal];
    }
}

$pageTitle = 'My Cart';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3">Cart</h1>
    <a class="btn btn-outline-secondary" href="<?= e(base_url('user/home.php')); ?>">Continue Shopping</a>
</div>
<?php if (!$items): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
<table class="table table-bordered">
<thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
<tbody>
<?php foreach ($items as $item): $p=$item['product']; ?>
<tr>
<td><?= e($p['name']); ?></td>
<td>$<?= number_format((float)$p['price'],2); ?></td>
<td>
<form method="post" class="d-flex gap-2">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>"><input type="hidden" name="product_id" value="<?= (int)$p['id']; ?>"><input type="number" class="form-control" style="width:100px" min="1" max="<?= (int)$p['stock']; ?>" name="quantity" value="<?= (int)$item['quantity']; ?>">
<button class="btn btn-sm btn-primary" name="action" value="update">Update</button>
<button class="btn btn-sm btn-danger" name="action" value="remove">Remove</button>
</form>
</td>
<td>$<?= number_format((float)$item['subtotal'],2); ?></td>
<td></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<div class="text-end">
    <h4>Total: $<?= number_format($total,2); ?></h4>
    <a class="btn btn-success" href="<?= e(base_url('user/checkout.php')); ?>">Proceed to Checkout</a>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
