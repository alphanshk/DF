<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid token.');
        redirect('/admin/users.php');
    }
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    if ($id > 0 && in_array($status, ['active', 'blocked', 'pending'], true)) {
        $stmt = $pdo->prepare('UPDATE users SET status=? WHERE id=?');
        $stmt->execute([$status, $id]);
        set_flash('success', 'User status updated.');
    }
    redirect('/admin/users.php');
}

$users = $pdo->query('SELECT id,name,email,role,status,created_at FROM users ORDER BY created_at DESC')->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h3>Manage Users</h3>
<table class="table table-hover table-sm">
<tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr>
<?php foreach ($users as $u): ?>
<tr>
<td><?= e($u['name']) ?></td>
<td><?= e($u['email']) ?></td>
<td><?= e($u['role']) ?></td>
<td><?= e($u['status']) ?></td>
<td>
<form method="post" class="d-flex gap-2">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
<select name="status" class="form-select form-select-sm">
<?php foreach (['active', 'blocked', 'pending'] as $opt): ?>
<option value="<?= $opt ?>" <?= $u['status'] === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
<?php endforeach; ?>
</select>
<button class="btn btn-primary btn-sm">Save</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
