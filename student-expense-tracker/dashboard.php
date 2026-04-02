<?php
require 'includes/auth.php';
requireLogin();
require 'config/db.php';
require 'includes/functions.php';
include 'includes/header.php';

$studentId = $_SESSION['student_id'];
$currentMonth = date('Y-m');

$monthlyExpenseStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE student_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
$monthlyExpenseStmt->execute([$studentId, $currentMonth]);
$monthlyTotal = (float)$monthlyExpenseStmt->fetch()['total'];

$allowanceStmt = $pdo->prepare('SELECT monthly_limit FROM allowance WHERE student_id = ?');
$allowanceStmt->execute([$studentId]);
$allowance = (float)($allowanceStmt->fetch()['monthly_limit'] ?? 0);
$remaining = $allowance - $monthlyTotal;

$recentStmt = $pdo->prepare('SELECT * FROM expenses WHERE student_id = ? ORDER BY date DESC, id DESC LIMIT 5');
$recentStmt->execute([$studentId]);
$recentExpenses = $recentStmt->fetchAll();

$dailyStmt = $pdo->prepare('SELECT DATE(date) as day, SUM(amount) as total FROM expenses WHERE student_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(date) ORDER BY day ASC');
$dailyStmt->execute([$studentId]);
$dailyData = $dailyStmt->fetchAll();
?>

<h1>Hello, <?= htmlspecialchars($_SESSION['student_name']); ?> 👋</h1>

<div class="cards">
    <div class="card">
        <h3>This Month Expense</h3>
        <p><?= formatAmount($monthlyTotal); ?></p>
    </div>
    <div class="card">
        <h3>Monthly Allowance</h3>
        <p><?= formatAmount($allowance); ?></p>
    </div>
    <div class="card <?= $remaining < 0 ? 'danger' : ''; ?>">
        <h3>Remaining Balance</h3>
        <p><?= formatAmount($remaining); ?></p>
        <?php if ($remaining < 0): ?>
            <small>⚠ You have exceeded your allowance!</small>
        <?php endif; ?>
    </div>
</div>

<section class="panel">
    <h2>Recent Expenses</h2>
    <table>
        <thead>
        <tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th></tr>
        </thead>
        <tbody>
        <?php foreach ($recentExpenses as $expense): ?>
            <tr>
                <td><?= htmlspecialchars($expense['date']); ?></td>
                <td><?= htmlspecialchars($expense['category']); ?></td>
                <td><?= htmlspecialchars($expense['description']); ?></td>
                <td><?= formatAmount((float)$expense['amount']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="panel">
    <h2>Weekly Spending Summary</h2>
    <ul>
        <?php if (!$dailyData): ?>
            <li>No spending data found for the last 7 days.</li>
        <?php else: ?>
            <?php foreach ($dailyData as $day): ?>
                <li><?= htmlspecialchars($day['day']); ?>: <?= formatAmount((float)$day['total']); ?></li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</section>

<?php include 'includes/footer.php'; ?>
