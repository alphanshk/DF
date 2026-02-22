<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('user');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid token.');
        redirect('/user/cart.php');
    }
    if (isset($_POST['remove'])) {
        $id = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$id]);
    } elseif (isset($_POST['update'])) {
        $id = (int)$_POST['product_id'];
        $qty = max(1, (int)$_POST['qty']);
        $_SESSION['cart'][$id] = $qty;
    }
    redirect('/user/cart.php');
}

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $products = $pdo->query("SELECT id,name,price,stock,image FROM products WHERE id IN ({$ids})")->fetchAll();
    foreach ($products as $p) {
        $qty = min($cart[$p['id']] ?? 1, (int)$p['stock']);
        $line = $qty * (float)$p['price'];
        $total += $line;
        $items[] = ['product' => $p, 'qty' => $qty, 'line' => $line];
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h3>Shopping Cart</h3>
<?php if (!$items): ?>
<p>Your cart is empty. <a href="/user/home.php">Browse products</a>.</p>
<?php else: ?>
<table class="table table-bordered">
<tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
<?php foreach ($items as $item): $p = $item['product']; ?>
<tr>
<td><?= e($p['name']) ?></td>
<td>$<?= number_format((float)$p['price'],2) ?></td>
<td>
<form method="post" class="d-flex gap-2">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
<input type="number" class="form-control" name="qty" min="1" max="<?= (int)$p['stock'] ?>" value="<?= (int)$item['qty'] ?>">
<button class="btn btn-sm btn-primary" name="update">Update</button>
</form>
</td>
<td>$<?= number_format($item['line'],2) ?></td>
<td>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><button class="btn btn-sm btn-danger" name="remove">Remove</button></form>
</td>
</tr>
<?php endforeach; ?>
<tr><th colspan="3" class="text-end">Total</th><th>$<?= number_format($total,2) ?></th><th></th></tr>
</table>
<a class="btn btn-success" href="/user/checkout.php">Proceed to Checkout</a>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
