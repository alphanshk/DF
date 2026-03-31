<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

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

$stmt = $pdo->prepare('DELETE FROM expenses WHERE id = :id AND user_id = :user_id');
$stmt->execute([
    'id' => $id,
    'user_id' => currentUserId(),
]);

echo json_encode(['success' => true, 'message' => 'Expense deleted successfully']);
