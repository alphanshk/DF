<?php
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? 0;
$description = trim($input['description'] ?? '');
$amount = $input['amount'] ?? '';
$type = $input['type'] ?? '';
$category = trim($input['category'] ?? '');
$date = $input['date'] ?? '';

if (!is_numeric($id) || (int)$id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid transaction id.']);
    exit;
}

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

$id = (int)$id;
$stmt = $conn->prepare('UPDATE transactions SET description = ?, amount = ?, type = ?, category = ?, date = ? WHERE id = ?');
$stmt->bind_param('sdsssi', $description, $amount, $type, $category, $date, $id);
$stmt->execute();

if ($stmt->affected_rows >= 0) {
    echo json_encode(['success' => true, 'message' => 'Transaction updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update transaction.']);
}

$stmt->close();
$conn->close();
?>
