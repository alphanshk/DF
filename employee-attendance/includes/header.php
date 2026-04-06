<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
$isLoggedIn = !empty($_SESSION['user_id']);
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multi Employee Attendance</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Attendance App</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto">
        <?php if ($isLoggedIn): ?>
          <li class="nav-item"><a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link <?= $current === 'employees.php' ? 'active' : '' ?>" href="employees.php">Employees</a></li>
          <li class="nav-item"><a class="nav-link <?= $current === 'attendance.php' ? 'active' : '' ?>" href="attendance.php">Attendance</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-4">
  <?php if ($m = get_flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
  <?php if ($m = get_flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
