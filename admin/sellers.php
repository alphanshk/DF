<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid request token.');
        redirect('/admin/sellers.php');
    }
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    if (in_array($status, ['active', 'blocked', 'pending'], true) && $id > 0) {
        $stmt = $pdo->prepare("UPDATE users SET status=? WHERE id=? AND role='seller'");
        $stmt->execute([$status, $id]);
        set_flash('success', 'Seller status updated.');
    }
    redirect('/admin/sellers.php');
}

$sellers = $pdo->query("SELECT id,name,email,status,created_at FROM users WHERE role='seller' ORDER BY created_at DESC")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<h3>Sellers</h3>
<table class="table table-bordered">
    <tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
    <?php foreach ($sellers as $seller): ?>
    <tr>
        <td><?= e($seller['name']) ?></td>
        <td><?= e($seller['email']) ?></td>
        <td><?= e($seller['status']) ?></td>
        <td>
            <form method="post" class="d-flex gap-2">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$seller['id'] ?>">
                <select name="status" class="form-select form-select-sm">
                    <?php foreach (['pending', 'active', 'blocked'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $seller['status'] === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-primary">Save</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../includes/footer.php'; ?>
