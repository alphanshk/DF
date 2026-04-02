<?php
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? 0;

if (!is_numeric($id) || (int)$id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid transaction id.']);
    exit;
}

$id = (int)$id;
$stmt = $conn->prepare('DELETE FROM transactions WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully.']);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Transaction not found.']);
}

$stmt->close();
$conn->close();
?>
