<?php
// Start session for authentication and flash messages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials for XAMPP default setup.
$host = 'localhost';
$dbName = 'smart_expense_tracker';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

try {
    // Create PDO connection with proper error mode and default fetch mode.
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
