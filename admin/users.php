<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('admin/users.php');
    }
    $userId = (int) ($_POST['user_id'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    if ($userId > 0 && in_array($status, ['active', 'blocked', 'pending'], true)) {
        $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id AND role = 'user'");
        $stmt->execute(['status' => $status, 'id' => $userId]);
        set_flash('success', 'User status updated.');
    }
    redirect('admin/users.php');
}

$users = $pdo->query("SELECT id, name, email, status, created_at FROM users WHERE role='user' ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Manage Users</h1>
<table class="table table-bordered table-striped">
    <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= e($user['name']); ?></td>
            <td><?= e($user['email']); ?></td>
            <td><?= e($user['status']); ?></td>
            <td><?= e($user['created_at']); ?></td>
            <td>
                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="user_id" value="<?= (int) $user['id']; ?>">
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
