<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $token = $_POST['csrf_token'] ?? null;

    if (!verify_csrf($token)) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('/auth/register.php');
    }

    if (!in_array($role, ['user', 'seller'], true)) {
        $role = 'user';
    }

    if ($name === '' || !$email || strlen($password) < 6) {
        set_flash('danger', 'Valid name, email and password (min 6 chars) are required.');
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) {
            set_flash('danger', 'Email already exists.');
        } else {
            $status = $role === 'seller' ? 'pending' : 'active';
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role,status,created_at) VALUES (?,?,?,?,?,NOW())');
            $stmt->execute([$name, $email, $hash, $role, $status]);
            set_flash('success', $role === 'seller' ? 'Seller account created and pending admin approval.' : 'Registration successful. Please login.');
            redirect('/auth/login.php');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Register</h4>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Register as</label>
                        <select name="role" class="form-select">
                            <option value="user">User</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                    <button class="btn btn-success w-100">Create Account</button>
                </form>
                <p class="mt-3 mb-0"><a href="/auth/login.php">Back to login</a></p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
