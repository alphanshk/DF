<?php
require_once __DIR__ . '/../config/helpers.php';
$flashMessages = get_flash_messages();
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'E-Commerce'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(base_url('assets/style.css')); ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= e(base_url('user/home.php')); ?>">ShopHub</a>
        <div class="ms-auto text-light">
            <?php if ($user): ?>
                <span class="me-3">Logged in as <?= e($user['name']); ?> (<?= e($user['role']); ?>)</span>
                <a class="btn btn-outline-light btn-sm" href="<?= e(base_url('auth/logout.php')); ?>">Logout</a>
            <?php else: ?>
                <a class="btn btn-outline-light btn-sm" href="<?= e(base_url('auth/login.php')); ?>">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container">
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?= e($type); ?> alert-dismissible fade show" role="alert">
                <?= e($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
