<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

/** Generate and return CSRF token for forms. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/** Verify CSRF token for state-changing requests. */
function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
}

/** Escape output. */
function esc(string|int|float|null $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Build app-aware URL path. */
function app_url(string $path = ''): string
{
    return APP_URL . $path;
}

/** Redirect helper. */
function redirect_to(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

/** Flash message helpers. */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Currently authenticated user. */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** Guard: require logged-in user. */
function require_login(): void
{
    if (!current_user()) {
        set_flash('danger', 'Please login first.');
        redirect_to('/auth/login.php');
    }
}

/** Guard: require exact role. */
function require_role(string $role): void
{
    require_login();

    if ((current_user()['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Unauthorized access');
    }
}

/** Redirect user by role after login. */
function redirect_by_role(string $role): never
{
    $routes = [
        'admin' => '/admin/dashboard.php',
        'seller' => '/seller/dashboard.php',
        'user' => '/user/home.php',
    ];

    redirect_to($routes[$role] ?? '/auth/login.php');
}
