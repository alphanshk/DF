<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if (is_logged_in()) {
    role_redirect($_SESSION['user']['role']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('auth/login.php');
    }

    if (!validate_email($email) || $password === '') {
        set_flash('danger', 'Enter a valid email and password.');
        redirect('auth/login.php');
    }

    $stmt = $pdo->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        set_flash('danger', 'Invalid credentials.');
        redirect('auth/login.php');
    }

    if ($user['status'] !== 'active') {
        set_flash('warning', 'Your account is not active. Please contact admin.');
        redirect('auth/login.php');
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    set_flash('success', 'Welcome back, ' . $user['name'] . '!');
    role_redirect($user['role']);
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h4 mb-3">Login</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Login</button>
                </form>
                <p class="mt-3 mb-0 text-center">No account? <a href="<?= e(base_url('auth/register.php')); ?>">Register</a></p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
