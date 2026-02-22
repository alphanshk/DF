<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('auth/register.php');
    }

    if ($name === '' || !validate_email($email) || strlen($password) < 6 || !in_array($role, ['user', 'seller'], true)) {
        set_flash('danger', 'Please fill all fields correctly.');
        redirect('auth/register.php');
    }

    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $check->execute(['email' => $email]);
    if ($check->fetch()) {
        set_flash('danger', 'Email already exists.');
        redirect('auth/register.php');
    }

    $status = $role === 'seller' ? 'pending' : 'active';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status, created_at) VALUES (:name, :email, :password, :role, :status, NOW())');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hash,
        'role' => $role,
        'status' => $status,
    ]);

    set_flash('success', $role === 'seller' ? 'Seller account created and pending admin approval.' : 'Account created. You can login now.');
    redirect('auth/login.php');
}

$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h4 mb-3">Register</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (min 6 chars)</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Register As</label>
                        <select name="role" class="form-select" required>
                            <option value="user">User</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                    <button class="btn btn-success w-100" type="submit">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
