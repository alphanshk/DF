<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function base_url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($base === '' || $base === '.' || $base === '/') {
        return '/' . ltrim($path, '/');
    }

    return $base . '/' . ltrim($path, '/');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][$type][] = $message;
}

function get_flash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flash;
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
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        set_flash('danger', 'Please login first.');
        redirect('/auth/login.php');
    }
}

function require_role(string $role): void
{
    require_login();
    $user = current_user();
    if (($user['role'] ?? '') !== $role) {
        set_flash('danger', 'Unauthorized access.');
        redirect('/auth/login.php');
    }
}

function paginate(int $totalRows, int $perPage = 10): array
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    return [$page, $totalPages, $offset];
}
