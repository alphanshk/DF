<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please fill in email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Expense Tracker - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Smart Expense Tracker</h1>
        <p class="subtitle">Welcome back! Please login.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm" novalidate>
            <label>Email</label>
            <input type="email" name="email" required placeholder="you@example.com">

            <label>Password</label>
            <input type="password" name="password" required minlength="8" placeholder="••••••••">

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p class="auth-link">Don't have an account? <a href="register.php">Register</a></p>
    </div>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
