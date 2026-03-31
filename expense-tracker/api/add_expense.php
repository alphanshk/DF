<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/expense_functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

[$valid, $message] = validateExpenseInput($_POST);
if (!$valid) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$userId = currentUserId();
$stmt = $pdo->prepare('INSERT INTO expenses (user_id, title, amount, category, date) VALUES (:user_id, :title, :amount, :category, :date)');
$stmt->execute([
    'user_id' => $userId,
    'title' => trim($_POST['title']),
    'amount' => (float) $_POST['amount'],
    'category' => trim($_POST['category']),
    'date' => trim($_POST['date']),
]);

echo json_encode(['success' => true, 'message' => 'Expense added successfully']);
