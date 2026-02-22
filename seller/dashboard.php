<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('seller');

$sellerId = (int)current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_product' || $action === 'update_product') {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $imageName = $_POST['current_image'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if ($_FILES['image']['size'] > 2 * 1024 * 1024 || !in_array(mime_content_type($_FILES['image']['tmp_name']), $allowed, true)) {
                set_flash('danger', 'Invalid image. Use jpg/png/webp up to 2MB.');
                redirect_to('/seller/dashboard.php');
                exit;
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0775, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $imageName);
        }

        if ($name !== '' && $price > 0 && $stock >= 0 && $categoryId > 0) {
            if ($action === 'add_product') {
                $stmt = $pdo->prepare('INSERT INTO products (seller_id, name, price, stock, image, category_id) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$sellerId, $name, $price, $stock, $imageName, $categoryId]);
                set_flash('success', 'Product added.');
            } else {
                $stmt = $pdo->prepare('UPDATE products SET name=?, price=?, stock=?, image=?, category_id=? WHERE id=? AND seller_id=?');
                $stmt->execute([$name, $price, $stock, $imageName, $categoryId, $productId, $sellerId]);
                set_flash('success', 'Product updated.');
            }
        }
    }

    if ($action === 'delete_product') {
        $id = (int)($_POST['product_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ? AND seller_id = ?');
        $stmt->execute([$id, $sellerId]);
        set_flash('success', 'Product deleted.');
    }

    redirect_to('/seller/dashboard.php');
    exit;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$products = $pdo->prepare('SELECT p.*, c.name as category FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE seller_id = ? ORDER BY id DESC');
$products->execute([$sellerId]);
$products = $products->fetchAll();

$orders = $pdo->prepare('SELECT o.id, o.status, o.created_at, oi.quantity, oi.price, p.name
    FROM orders o
    JOIN order_items oi ON o.id=oi.order_id
    JOIN products p ON oi.product_id=p.id
    WHERE p.seller_id=? ORDER BY o.id DESC');
$orders->execute([$sellerId]);
$sellerOrders = $orders->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h3>Seller Dashboard</h3>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card"><div class="card-body">
      <h5>Add Product</h5>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="add_product">
        <input class="form-control mb-2" name="name" placeholder="Name" required>
        <input class="form-control mb-2" name="price" type="number" min="0.01" step="0.01" placeholder="Price" required>
        <input class="form-control mb-2" name="stock" type="number" min="0" placeholder="Stock" required>
        <select class="form-select mb-2" name="category_id" required>
          <option value="">Select category</option>
          <?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>"><?= esc($cat['name']) ?></option><?php endforeach; ?>
        </select>
        <input class="form-control mb-2" type="file" name="image" accept="image/*">
        <button class="btn btn-primary">Add Product</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <div class="card"><div class="card-body">
      <h5>My Products / Inventory</h5>
      <div class="table-responsive"><table class="table table-sm align-middle">
        <tr><th>Image</th><th>Name</th><th>Price</th><th>Stock</th><th>Category</th><th>Action</th></tr>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?php if ($p['image']): ?><img src="<?= esc(UPLOAD_URL . $p['image']) ?>" width="48"><?php endif; ?></td>
            <td><?= esc($p['name']) ?></td><td>$<?= number_format((float)$p['price'],2) ?></td><td><?= (int)$p['stock'] ?></td><td><?= esc($p['category']) ?></td>
            <td>
              <details>
                <summary class="btn btn-sm btn-outline-secondary">Edit</summary>
                <form method="post" enctype="multipart/form-data" class="mt-2">
                  <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="update_product"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="current_image" value="<?= esc($p['image']) ?>">
                  <input class="form-control form-control-sm mb-1" name="name" value="<?= esc($p['name']) ?>" required>
                  <input class="form-control form-control-sm mb-1" name="price" type="number" step="0.01" min="0.01" value="<?= esc((string)$p['price']) ?>" required>
                  <input class="form-control form-control-sm mb-1" name="stock" type="number" min="0" value="<?= (int)$p['stock'] ?>" required>
                  <select class="form-select form-select-sm mb-1" name="category_id"><?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= (int)$cat['id']===(int)$p['category_id']?'selected':'' ?>><?= esc($cat['name']) ?></option><?php endforeach; ?></select>
                  <input class="form-control form-control-sm mb-1" type="file" name="image" accept="image/*">
                  <button class="btn btn-sm btn-primary">Save</button>
                </form>
              </details>
              <form method="post" class="mt-1">
                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table></div>
    </div></div>
  </div>
  <div class="col-12">
    <div class="card"><div class="card-body">
      <h5>My Orders</h5>
      <div class="table-responsive"><table class="table table-sm">
        <tr><th>Order #</th><th>Product</th><th>Qty</th><th>Price</th><th>Status</th><th>Date</th></tr>
        <?php foreach ($sellerOrders as $o): ?>
          <tr><td><?= (int)$o['id'] ?></td><td><?= esc($o['name']) ?></td><td><?= (int)$o['quantity'] ?></td><td>$<?= number_format((float)$o['price'],2) ?></td><td><?= esc($o['status']) ?></td><td><?= esc($o['created_at']) ?></td></tr>
        <?php endforeach; ?>
      </table></div>
    </div></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
