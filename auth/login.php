<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if (current_user()) {
    redirect('/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? null;

    if (!verify_csrf($token)) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('/auth/login.php');
    }

    if (!$email || !$password) {
        set_flash('danger', 'Email and password are required.');
    } else {
        $stmt = $pdo->prepare('SELECT id,name,email,password,role,status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            set_flash('danger', 'Invalid credentials.');
        } elseif ($user['status'] !== 'active') {
            set_flash('warning', 'Your account is not active. Current status: ' . $user['status']);
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];

            if ($user['role'] === 'admin') redirect('/admin/dashboard.php');
            if ($user['role'] === 'seller') redirect('/seller/dashboard.php');
            redirect('/user/home.php');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Login</h4>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
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
                <p class="mt-3 mb-0">No account? <a href="/auth/register.php">Register here</a></p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
