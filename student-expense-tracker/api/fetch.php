<?php
require_once 'db.php';

$sql = 'SELECT id, description, amount, type, category, date FROM transactions ORDER BY date DESC, id DESC';
$result = $conn->query($sql);

$transactions = [];
$totalIncome = 0;
$totalExpense = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['amount'] = (float)$row['amount'];
        $transactions[] = $row;

        if ($row['type'] === 'income') {
            $totalIncome += $row['amount'];
        } else {
            $totalExpense += $row['amount'];
        }
    }
}

echo json_encode([
    'success' => true,
    'transactions' => $transactions,
    'summary' => [
        'income' => round($totalIncome, 2),
        'expense' => round($totalExpense, 2),
        'balance' => round($totalIncome - $totalExpense, 2)
    ]
]);

$conn->close();
?>
