<?php

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function get_flash(string $type): ?string
{
    if (!isset($_SESSION['flash'][$type])) {
        return null;
    }

    $message = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);

    return $message;
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Please login first.');
        redirect('login.php');
    }
}
