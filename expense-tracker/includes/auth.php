<?php
/**
 * Authentication and session helpers.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function redirectIfLoggedIn(): void
{
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit;
    }
}

function currentUserName(): string
{
    return $_SESSION['user_name'] ?? 'User';
}

function currentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}
