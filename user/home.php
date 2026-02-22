<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('user');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid token.');
        redirect('/user/home.php');
    }
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $stmt = $pdo->prepare('SELECT id,stock FROM products WHERE id=?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if ($product && $product['stock'] >= $qty) {
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
        set_flash('success', 'Product added to cart.');
    } else {
        set_flash('danger', 'Product unavailable.');
    }
    redirect('/user/home.php');
}

$search = trim($_GET['search'] ?? '');
$categoryId = (int)($_GET['category_id'] ?? 0);
$where = ' WHERE p.stock > 0 ';
$params = [];
if ($search !== '') {
    $where .= ' AND p.name LIKE ?';
    $params[] = "%{$search}%";
}
if ($categoryId > 0) {
    $where .= ' AND p.category_id = ?';
    $params[] = $categoryId;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p {$where}");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
[$page, $totalPages, $offset] = paginate($totalRows, 8);

$sql = "SELECT p.*, c.name category_name, u.name seller_name FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.seller_id = u.id
        {$where}
        ORDER BY p.id DESC
        LIMIT 8 OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = $pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Product Catalog</h3>
    <div>
        <a class="btn btn-outline-primary" href="/user/cart.php">Cart</a>
        <a class="btn btn-outline-secondary" href="/user/orders.php">Order History</a>
        <a class="btn btn-outline-dark" href="/user/profile.php">Profile</a>
    </div>
</div>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-6"><input class="form-control" name="search" placeholder="Search products" value="<?= e($search) ?>"></div>
    <div class="col-md-4">
        <select class="form-select" name="category_id">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="row g-3">
<?php foreach ($products as $product): ?>
    <div class="col-md-3">
        <div class="card h-100">
            <?php if ($product['image']): ?><img src="/uploads/<?= e($product['image']) ?>" class="card-img-top" alt="<?= e($product['name']) ?>"><?php endif; ?>
            <div class="card-body">
                <h6><?= e($product['name']) ?></h6>
                <p class="small text-muted mb-1">Category: <?= e($product['category_name'] ?? 'N/A') ?></p>
                <p class="small text-muted mb-1">Seller: <?= e($product['seller_name'] ?? 'N/A') ?></p>
                <p class="fw-bold">$<?= number_format((float)$product['price'],2) ?></p>
                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="number" name="qty" class="form-control form-control-sm" min="1" max="<?= (int)$product['stock'] ?>" value="1">
                    <button name="add_to_cart" value="1" class="btn btn-sm btn-success">Add</button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<nav class="mt-4">
<ul class="pagination">
<?php for ($i=1; $i <= $totalPages; $i++): ?>
<li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category_id=<?= $categoryId ?>"><?= $i ?></a></li>
<?php endfor; ?>
</ul>
</nav>
<?php include __DIR__ . '/../includes/footer.php'; ?>
