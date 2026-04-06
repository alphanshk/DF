<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
ensure_logged_in();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    set_flash('error', 'Invalid expense id.');
    redirect('expenses.php');
}

$sql = 'DELETE FROM expenses WHERE id = :id AND user_id = :user_id';
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'id' => $id,
    'user_id' => current_user_id(),
]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Expense deleted successfully.');
} else {
    set_flash('error', 'Expense not found or already deleted.');
}

redirect('expenses.php');
