<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function base_url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][$type][] = $message;
}

function get_flash_messages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return !empty($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(?string $role = null): void
{
    if (!is_logged_in()) {
        set_flash('danger', 'Please login first.');
        redirect('auth/login.php');
    }

    if ($role !== null && ($_SESSION['user']['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Forbidden: unauthorized access.');
    }
}

function role_redirect(string $role): void
{
    if ($role === 'admin') {
        redirect('admin/dashboard.php');
    }
    if ($role === 'seller') {
        redirect('seller/dashboard.php');
    }
    redirect('user/home.php');
}

function validate_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}
