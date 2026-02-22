<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'seller_status') {
        $sellerId = (int)($_POST['seller_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        if (in_array($status, ['active', 'blocked', 'pending'], true)) {
            $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ? AND role = "seller"');
            $stmt->execute([$status, $sellerId]);
            set_flash('success', 'Seller status updated.');
        }
    }

    if ($action === 'user_status') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        if (in_array($status, ['active', 'blocked'], true)) {
            $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ? AND role IN ("user", "seller")');
            $stmt->execute([$status, $userId]);
            set_flash('success', 'User status changed.');
        }
    }

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$name]);
            set_flash('success', 'Category added.');
        }
    }

    if ($action === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Category deleted.');
    }

    redirect_to('/admin/dashboard.php');
    exit;
}

$stats = [
    'users' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
    'sellers' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='seller'")->fetchColumn(),
    'products' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'revenue' => (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('paid','shipped','delivered')")->fetchColumn(),
];

$sellers = $pdo->query("SELECT id, name, email, status, created_at FROM users WHERE role='seller' ORDER BY created_at DESC")->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$users = $pdo->query("SELECT id, name, email, role, status FROM users WHERE role IN ('user','seller') ORDER BY id DESC LIMIT 20")->fetchAll();
$orders = $pdo->query('SELECT o.id, u.name AS customer, o.total_amount, o.status, o.created_at FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.id DESC LIMIT 20')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3">Admin Dashboard</h3>
<div class="row g-3 mb-4">
<?php foreach ($stats as $key => $value): ?>
  <div class="col-md-2">
    <div class="card text-bg-dark"><div class="card-body">
      <h6 class="text-capitalize"><?= esc($key) ?></h6>
      <p class="mb-0 fw-bold"><?= $key === 'revenue' ? '$' . number_format($value, 2) : esc((string)$value) ?></p>
    </div></div>
  </div>
<?php endforeach; ?>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5>Approve / Block Sellers</h5>
      <div class="table-responsive"><table class="table table-sm">
        <tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
        <?php foreach ($sellers as $seller): ?>
          <tr>
            <td><?= esc($seller['name']) ?></td><td><?= esc($seller['email']) ?></td><td><?= esc($seller['status']) ?></td>
            <td>
              <form method="post" class="d-flex gap-1">
                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                <input type="hidden" name="action" value="seller_status">
                <input type="hidden" name="seller_id" value="<?= (int)$seller['id'] ?>">
                <select name="status" class="form-select form-select-sm">
                  <option value="active">active</option><option value="pending">pending</option><option value="blocked">blocked</option>
                </select>
                <button class="btn btn-primary btn-sm">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table></div>
    </div></div>
  </div>

  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5>Manage Categories</h5>
      <form method="post" class="d-flex gap-2 mb-3">
        <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="add_category">
        <input type="text" name="name" class="form-control" placeholder="Category name" required><button class="btn btn-success">Add</button>
      </form>
      <?php foreach ($categories as $cat): ?>
        <form method="post" class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
          <span><?= esc($cat['name']) ?></span>
          <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
          <button class="btn btn-outline-danger btn-sm">Delete</button>
        </form>
      <?php endforeach; ?>
    </div></div>
  </div>

  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5>Manage Users</h5>
      <div class="table-responsive"><table class="table table-sm">
        <tr><th>Name</th><th>Role</th><th>Status</th><th>Action</th></tr>
        <?php foreach ($users as $u): ?>
          <tr><td><?= esc($u['name']) ?></td><td><?= esc($u['role']) ?></td><td><?= esc($u['status']) ?></td><td>
            <form method="post" class="d-flex gap-1">
              <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>"><input type="hidden" name="action" value="user_status"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <select name="status" class="form-select form-select-sm"><option value="active">active</option><option value="blocked">blocked</option></select>
              <button class="btn btn-secondary btn-sm">Update</button>
            </form>
          </td></tr>
        <?php endforeach; ?>
      </table></div>
    </div></div>
  </div>

  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5>All Orders</h5>
      <div class="table-responsive"><table class="table table-sm">
        <tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
        <?php foreach ($orders as $order): ?>
          <tr><td><?= (int)$order['id'] ?></td><td><?= esc($order['customer']) ?></td><td>$<?= number_format((float)$order['total_amount'],2) ?></td><td><?= esc($order['status']) ?></td><td><?= esc($order['created_at']) ?></td></tr>
        <?php endforeach; ?>
      </table></div>
    </div></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
