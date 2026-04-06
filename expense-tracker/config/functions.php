<?php

/**
 * Redirect user to given path.
 */
function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

/**
 * Escape output for safe HTML rendering.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message into session.
 */
function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

/**
 * Get and clear flash message from session.
 */
function get_flash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

/**
 * Ensure user is logged in before opening protected pages.
 */
function ensure_logged_in(): void
{
    if (empty($_SESSION['user_id'])) {
        set_flash('error', 'Please login to continue.');
        redirect('login.php');
    }
}

/**
 * Current logged in user id helper.
 */
function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}
