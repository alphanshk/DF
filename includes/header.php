<?php
require_once __DIR__ . '/../config/helpers.php';
$user = current_user();
$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/user/home.php">Shop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navBar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navBar">
            <ul class="navbar-nav ms-auto">
                <?php if ($user): ?>
                    <li class="nav-item"><span class="nav-link">Hi, <?= e($user['name']) ?></span></li>
                    <?php if ($user['role'] === 'admin'): ?><li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Admin</a></li><?php endif; ?>
                    <?php if ($user['role'] === 'seller'): ?><li class="nav-item"><a class="nav-link" href="/seller/dashboard.php">Seller</a></li><?php endif; ?>
                    <?php if ($user['role'] === 'user'): ?><li class="nav-item"><a class="nav-link" href="/user/home.php">Home</a></li><?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="/auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <?php foreach ($flash as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?= e($type) ?> alert-dismissible fade show" role="alert">
                <?= e($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
