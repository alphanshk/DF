<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        set_flash('error', 'All fields are required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Please enter a valid email address.');
    } elseif (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters long.');
    } elseif ($password !== $confirmPassword) {
        set_flash('error', 'Password confirmation does not match.');
    } else {
        $checkSql = 'SELECT id FROM users WHERE email = :email LIMIT 1';
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['email' => $email]);

        if ($checkStmt->fetch()) {
            set_flash('error', 'Email already exists. Please login.');
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $insertSql = 'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)';
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hash,
            ]);

            set_flash('success', 'Registration successful. Please login.');
            redirect('login.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="card-title mb-3 text-center">Create Account</h3>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= e($name) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <p class="mt-3 mb-0 text-center">Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
