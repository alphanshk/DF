<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login('user');

$userId = (int) $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('user/profile.php');
    }

    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '') {
        set_flash('danger', 'Name is required.');
        redirect('user/profile.php');
    }

    if ($password !== '' && strlen($password) < 6) {
        set_flash('danger', 'Password must be at least 6 characters.');
        redirect('user/profile.php');
    }

    if ($password !== '') {
        $stmt = $pdo->prepare('UPDATE users SET name=:name, password=:password WHERE id=:id');
        $stmt->execute(['name' => $name, 'password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $userId]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name=:name WHERE id=:id');
        $stmt->execute(['name' => $name, 'id' => $userId]);
    }

    $_SESSION['user']['name'] = $name;
    set_flash('success', 'Profile updated.');
    redirect('user/profile.php');
}

$stmt = $pdo->prepare('SELECT name, email FROM users WHERE id=:id');
$stmt->execute(['id' => $userId]);
$profile = $stmt->fetch();

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Profile</h1>
<form method="post" class="card p-3">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
    <div class="mb-2">
        <label class="form-label">Name</label>
        <input class="form-control" name="name" value="<?= e($profile['name']); ?>" required>
    </div>
    <div class="mb-2">
        <label class="form-label">Email</label>
        <input class="form-control" value="<?= e($profile['email']); ?>" disabled>
    </div>
    <div class="mb-3">
        <label class="form-label">New Password (optional)</label>
        <input class="form-control" type="password" name="password" minlength="6">
    </div>
    <button class="btn btn-primary">Save</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
