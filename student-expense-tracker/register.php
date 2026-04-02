<?php
session_start();
require 'config/db.php';
require 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $college = cleanInput($_POST['college_name'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = $pdo->prepare('SELECT id FROM students WHERE email = ?');
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'Email already registered. Please login.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO students (name, email, password, college_name) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hashedPassword, $college]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Expense Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-card">
    <h1>Create Account</h1>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error); ?></p><?php endif; ?>
    <form method="post">
        <label>Name *</label>
        <input type="text" name="name" required>

        <label>Email *</label>
        <input type="email" name="email" required>

        <label>Password *</label>
        <input type="password" name="password" required>

        <label>College Name (optional)</label>
        <input type="text" name="college_name">

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
