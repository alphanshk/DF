<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('admin/sellers.php');
    }

    $sellerId = (int) ($_POST['seller_id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';

    if ($sellerId > 0 && in_array($status, ['active', 'blocked', 'pending'], true)) {
        $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id AND role = 'seller'");
        $stmt->execute(['status' => $status, 'id' => $sellerId]);
        set_flash('success', 'Seller status updated.');
    }

    redirect('admin/sellers.php');
}

$sellers = $pdo->query("SELECT id, name, email, status, created_at FROM users WHERE role='seller' ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Manage Sellers';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Manage Sellers</h1>
<table class="table table-bordered table-striped">
    <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($sellers as $seller): ?>
        <tr>
            <td><?= e($seller['name']); ?></td>
            <td><?= e($seller['email']); ?></td>
            <td><span class="badge bg-secondary"><?= e($seller['status']); ?></span></td>
            <td><?= e($seller['created_at']); ?></td>
            <td>
                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="seller_id" value="<?= (int) $seller['id']; ?>">
                    <select class="form-select form-select-sm" name="status">
                        <option value="active">active</option>
                        <option value="blocked">blocked</option>
                        <option value="pending">pending</option>
                    </select>
                    <button class="btn btn-sm btn-primary">Update</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
