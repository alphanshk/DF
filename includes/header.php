<?php
require_once __DIR__ . '/auth.php';
$flashMessages = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(app_url('/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= esc(app_url('/user/home.php')) ?>"><?= esc(APP_NAME) ?></a>
        <div class="ms-auto d-flex gap-2">
            <?php if (current_user()): ?>
                <span class="text-white small mt-2"><?= esc(current_user()['name']) ?> (<?= esc(current_user()['role']) ?>)</span>
                <a class="btn btn-outline-light btn-sm" href="<?= esc(app_url('/auth/logout.php')) ?>">Logout</a>
            <?php else: ?>
                <a class="btn btn-outline-light btn-sm" href="<?= esc(app_url('/auth/login.php')) ?>">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container py-4">
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert-<?= esc($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= esc($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
