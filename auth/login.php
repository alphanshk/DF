<?php
require_once __DIR__ . '/../includes/auth.php';

if (current_user()) {
    redirect_by_role(current_user()['role']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || strlen($password) < 6) {
        set_flash('danger', 'Invalid credentials format.');
        redirect_to('/auth/login.php');
    }

    $stmt = $pdo->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        set_flash('danger', 'Email or password is incorrect.');
        redirect_to('/auth/login.php');
    }

    if ($user['status'] !== 'active') {
        set_flash('warning', 'Account is not active yet. Contact admin.');
        redirect_to('/auth/login.php');
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    set_flash('success', 'Welcome back, ' . $user['name'] . '!');
    redirect_by_role($user['role']);
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3 text-capitalize"><?= esc(APP_NAME) ?> Login</h4>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Login</button>
                </form>
                <hr>
                <p class="mb-0">New here? <a href="<?= esc(app_url('/auth/register.php')) ?>">Register User/Seller</a></p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
