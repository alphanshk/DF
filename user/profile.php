<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_role('user');
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Invalid token.');
        redirect('/user/profile.php');
    }
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '') {
        set_flash('danger', 'Name is required.');
    } else {
        if ($password !== '') {
            if (strlen($password) < 6) {
                set_flash('danger', 'Password must be at least 6 chars.');
                redirect('/user/profile.php');
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET name=?, password=? WHERE id=?');
            $stmt->execute([$name, $hash, $user['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name=? WHERE id=?');
            $stmt->execute([$name, $user['id']]);
        }
        $_SESSION['user']['name'] = $name;
        set_flash('success', 'Profile updated.');
        redirect('/user/profile.php');
    }
}

$stmt = $pdo->prepare('SELECT name,email,created_at FROM users WHERE id=?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();
include __DIR__ . '/../includes/header.php';
?>
<h3>My Profile</h3>
<form method="post" class="row g-3">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= e($profile['name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="<?= e($profile['email']) ?>" disabled></div>
<div class="col-md-6"><label class="form-label">New Password (optional)</label><input type="password" name="password" class="form-control" minlength="6"></div>
<div class="col-12"><button class="btn btn-primary">Save Profile</button></div>
</form>
<p class="text-muted mt-3">Joined: <?= e($profile['created_at']) ?></p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
