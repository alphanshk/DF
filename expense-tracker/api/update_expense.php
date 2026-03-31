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

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid expense ID']);
    exit;
}

[$valid, $message] = validateExpenseInput($_POST);
if (!$valid) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$sql = 'UPDATE expenses
        SET title = :title, amount = :amount, category = :category, date = :date
        WHERE id = :id AND user_id = :user_id';
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'title' => trim($_POST['title']),
    'amount' => (float) $_POST['amount'],
    'category' => trim($_POST['category']),
    'date' => trim($_POST['date']),
    'id' => $id,
    'user_id' => currentUserId(),
]);

echo json_encode(['success' => true, 'message' => 'Expense updated successfully']);
