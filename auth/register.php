<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if (!in_array($role, ['user', 'seller'], true)) {
        $role = 'user';
    }

    if ($name === '' || !$email || strlen($password) < 6) {
        set_flash('danger', 'Please fill valid registration details.');
        redirect_to('/auth/register.php');
    }

    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        set_flash('warning', 'Email already registered.');
        redirect_to('/auth/register.php');
    }

    $status = $role === 'seller' ? 'pending' : 'active';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare('INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $insert->execute([$name, $email, $hash, $role, $status]);

    set_flash('success', $role === 'seller' ? 'Seller account created and pending admin approval.' : 'Registration successful. Please login.');
    redirect_to('/auth/login.php');
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Register</h4>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (min 6 chars)</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Register As</label>
                        <select name="role" class="form-select">
                            <option value="user">User</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                    <button class="btn btn-success w-100">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
