<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('user');

$userId = (int)current_user()['id'];
$_SESSION['cart'] = $_SESSION['cart'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_to_cart') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
        set_flash('success', 'Product added to cart.');
    }

    if ($action === 'update_cart') {
        foreach (($_POST['qty'] ?? []) as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$pid]);
            } else {
                $_SESSION['cart'][$pid] = $qty;
            }
        }
        set_flash('success', 'Cart updated.');
    }

    if ($action === 'checkout') {
        if (!$_SESSION['cart']) {
            set_flash('warning', 'Cart is empty.');
            redirect_to('/user/home.php?view=cart');
            exit;
        }
        $ids = array_keys($_SESSION['cart']);
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, price, stock FROM products WHERE id IN ($in)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['id']] = $r;
        }

        $pdo->beginTransaction();
        try {
            $total = 0;
            foreach ($_SESSION['cart'] as $pid => $qty) {
                if (!isset($map[$pid]) || $map[$pid]['stock'] < $qty) {
                    throw new RuntimeException('Stock unavailable for product #' . $pid);
                }
                $total += $map[$pid]['price'] * $qty;
            }

            $o = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, "paid", NOW())');
            $o->execute([$userId, $total]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $itemStmt->execute([$orderId, $pid, $qty, $map[$pid]['price']]);
                $stockStmt->execute([$qty, $pid]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            set_flash('success', 'Order placed successfully. Tracking ID: #' . $orderId);
        } catch (Throwable $e) {
            $pdo->rollBack();
            set_flash('danger', 'Checkout failed: ' . $e->getMessage());
        }
    }

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
            $stmt->execute([$name, $userId]);
            $_SESSION['user']['name'] = $name;
            set_flash('success', 'Profile updated.');
        }
    }

    redirect_to('/user/home.php' . (!empty($_GET['view']) ? '?view=' . urlencode($_GET['view']) : ''));
}

$view = $_GET['view'] ?? 'products';
$search = trim($_GET['search'] ?? '');
$cat = (int)($_GET['category'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

$params = [];
$where = ' WHERE p.stock > 0 ';
if ($search !== '') {
    $where .= ' AND p.name LIKE ? ';
    $params[] = "%{$search}%";
}
if ($cat > 0) {
    $where .= ' AND p.category_id = ? ';
    $params[] = $cat;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where");
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalProducts / $limit));

$sql = "SELECT p.*, c.name as category, u.name as seller_name
        FROM products p
        LEFT JOIN categories c ON p.category_id=c.id
        LEFT JOIN users u ON p.seller_id=u.id
        $where ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$cartItems = [];
$cartTotal = 0;
if ($_SESSION['cart']) {
    $ids = array_keys($_SESSION['cart']);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $cstmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($in)");
    $cstmt->execute($ids);
    foreach ($cstmt->fetchAll() as $item) {
        $qty = (int)$_SESSION['cart'][$item['id']];
        $sub = $item['price'] * $qty;
        $cartTotal += $sub;
        $cartItems[] = ['id' => $item['id'], 'name' => $item['name'], 'price' => $item['price'], 'qty' => $qty, 'subtotal' => $sub];
    }
}

$orders = $pdo->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
$orders->execute([$userId]);
$orderHistory = $orders->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h3>User Home</h3>
<ul class="nav nav-pills mb-3">
  <li class="nav-item"><a class="nav-link <?= $view==='products'?'active':'' ?>" href="<?= esc(app_url('/user/home.php')) ?>">Products</a></li>
  <li class="nav-item"><a class="nav-link <?= $view==='cart'?'active':'' ?>" href="<?= esc(app_url('/user/home.php?view=cart')) ?>">Cart (<?= count($_SESSION['cart']) ?>)</a></li>
  <li class="nav-item"><a class="nav-link <?= $view==='orders'?'active':'' ?>" href="<?= esc(app_url('/user/home.php?view=orders')) ?>">Order History</a></li>
  <li class="nav-item"><a class="nav-link <?= $view==='profile'?'active':'' ?>" href="<?= esc(app_url('/user/home.php?view=profile')) ?>">Profile</a></li>
</ul>

<?php if ($view === 'products'): ?>
<form class="row g-2 mb-3" method="get">
  <input type="hidden" name="view" value="products">
  <div class="col-md-5"><input class="form-control" name="search" value="<?= esc($search) ?>" placeholder="Search products"></div>
  <div class="col-md-3"><select class="form-select" name="category"><option value="0">All categories</option><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= esc($c['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="row g-3">
  <?php foreach ($products as $p): ?>
  <div class="col-md-4">
    <div class="card h-100"><img class="card-img-top product-img" src="<?= $p['image'] ? esc(UPLOAD_URL . $p['image']) : 'https://via.placeholder.com/300x180?text=No+Image' ?>"><div class="card-body">
      <h6><?= esc($p['name']) ?></h6><p class="mb-1">$<?= number_format((float)$p['price'],2) ?> | Stock: <?= (int)$p['stock'] ?></p>
      <small class="text-muted">Category: <?= esc($p['category']) ?> | Seller: <?= esc($p['seller_name']) ?></small>
      <form method="post" class="mt-2 d-flex gap-2">
        <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="add_to_cart"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <input type="number" class="form-control" name="quantity" min="1" max="<?= (int)$p['stock'] ?>" value="1">
        <button class="btn btn-success btn-sm">Add</button>
      </form>
    </div></div>
  </div>
  <?php endforeach; ?>
</div>
<nav class="mt-3"><ul class="pagination">
  <?php for ($i=1; $i <= $totalPages; $i++): ?>
    <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="?view=products&search=<?= urlencode($search) ?>&category=<?= $cat ?>&page=<?= $i ?>"><?= $i ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>

<?php if ($view === 'cart'): ?>
<form method="post">
  <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="update_cart">
  <table class="table"><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
  <?php foreach ($cartItems as $item): ?>
    <tr><td><?= esc($item['name']) ?></td><td>$<?= number_format((float)$item['price'],2) ?></td><td><input type="number" name="qty[<?= (int)$item['id'] ?>]" value="<?= (int)$item['qty'] ?>" min="0" class="form-control"></td><td>$<?= number_format((float)$item['subtotal'],2) ?></td></tr>
  <?php endforeach; ?>
  </table>
  <p class="fw-bold">Total: $<?= number_format((float)$cartTotal,2) ?></p>
  <button class="btn btn-outline-primary">Update Cart</button>
</form>
<form method="post" class="mt-2">
  <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="checkout">
  <button class="btn btn-success">Checkout</button>
</form>
<?php endif; ?>

<?php if ($view === 'orders'): ?>
<table class="table"><tr><th>Order #</th><th>Total</th><th>Status (Tracking)</th><th>Date</th></tr>
<?php foreach ($orderHistory as $o): ?><tr><td>#<?= (int)$o['id'] ?></td><td>$<?= number_format((float)$o['total_amount'],2) ?></td><td><?= esc($o['status']) ?></td><td><?= esc($o['created_at']) ?></td></tr><?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($view === 'profile'): ?>
<div class="card col-md-6"><div class="card-body">
<form method="post">
  <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="update_profile">
  <label class="form-label">Name</label><input class="form-control mb-2" name="name" value="<?= esc(current_user()['name']) ?>" required>
  <label class="form-label">Email</label><input class="form-control mb-2" value="<?= esc(current_user()['email']) ?>" disabled>
  <button class="btn btn-primary">Save</button>
</form>
</div></div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
