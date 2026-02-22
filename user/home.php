<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('user');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('user/home.php');
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));

    $stmt = $pdo->prepare('SELECT id, stock FROM products WHERE id=:id');
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch();

    if (!$product || (int) $product['stock'] < $qty) {
        set_flash('danger', 'Product unavailable or insufficient stock.');
        redirect('user/home.php');
    }

    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
    set_flash('success', 'Product added to cart.');
    redirect('user/home.php');
}

$search = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;

$where = ['p.stock > 0'];
$params = [];
if ($search !== '') {
    $where[] = 'p.name LIKE :search';
    $params['search'] = '%' . $search . '%';
}
if ($categoryId > 0) {
    $where[] = 'p.category_id = :category_id';
    $params['category_id'] = $categoryId;
}
$whereSql = implode(' AND ', $where);

$countSql = "SELECT COUNT(*) FROM products p WHERE {$whereSql}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalItems = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / $limit));

$sql = "SELECT p.*, c.name AS category_name, u.name AS seller_name FROM products p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN users u ON u.id = p.seller_id WHERE {$whereSql} ORDER BY p.id DESC LIMIT {$limit} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Shop Products';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Browse Products</h1>
    <div>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('user/cart.php')); ?>">Cart (<?= (int) array_sum($_SESSION['cart'] ?? []); ?>)</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('user/orders.php')); ?>">Orders</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= e(base_url('user/profile.php')); ?>">Profile</a>
    </div>
</div>
<form method="get" class="row g-2 mb-3">
    <div class="col-md-5"><input class="form-control" name="search" value="<?= e($search); ?>" placeholder="Search products..."></div>
    <div class="col-md-3"><select class="form-select" name="category_id"><option value="0">All Categories</option><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id']; ?>" <?= $categoryId===(int)$c['id']?'selected':''; ?>><?= e($c['name']); ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="row g-3">
<?php foreach ($products as $product): ?>
<div class="col-md-3">
    <div class="card h-100">
        <?php if ($product['image']): ?><img src="<?= e(base_url('uploads/' . $product['image'])); ?>" class="card-img-top" style="height:180px;object-fit:cover"><?php endif; ?>
        <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= e($product['name']); ?></h5>
            <p class="small text-muted mb-1">Category: <?= e($product['category_name'] ?? 'Uncategorized'); ?></p>
            <p class="small text-muted mb-2">Seller: <?= e($product['seller_name'] ?? 'N/A'); ?></p>
            <p class="fw-bold">$<?= number_format((float)$product['price'],2); ?></p>
            <form method="post" class="mt-auto">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                <input type="hidden" name="product_id" value="<?= (int)$product['id']; ?>">
                <input type="number" name="quantity" class="form-control mb-2" min="1" max="<?= (int)$product['stock']; ?>" value="1">
                <button class="btn btn-success w-100">Add to Cart</button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<nav class="mt-4"><ul class="pagination"><?php for($i=1;$i<=$totalPages;$i++): ?><li class="page-item <?= $i===$page?'active':''; ?>"><a class="page-link" href="?search=<?= urlencode($search); ?>&category_id=<?= $categoryId; ?>&page=<?= $i; ?>"><?= $i; ?></a></li><?php endfor; ?></ul></nav>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
