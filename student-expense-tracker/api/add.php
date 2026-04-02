<?php
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

$description = trim($input['description'] ?? '');
$amount = $input['amount'] ?? '';
$type = $input['type'] ?? '';
$category = trim($input['category'] ?? '');
$date = $input['date'] ?? '';

if ($description === '' || $amount === '' || $type === '' || $category === '' || $date === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!is_numeric($amount) || (float)$amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Amount must be a valid number greater than 0.']);
    exit;
}

if (!in_array($type, ['income', 'expense'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Type must be income or expense.']);
    exit;
}

$stmt = $conn->prepare('INSERT INTO transactions (description, amount, type, category, date) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sdsss', $description, $amount, $type, $category, $date);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Transaction added successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add transaction.']);
}

$stmt->close();
$conn->close();
?>
