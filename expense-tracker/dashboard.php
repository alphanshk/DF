<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
ensure_logged_in();

$userId = current_user_id();
$currentMonth = date('Y-m');

$totalSql = 'SELECT COALESCE(SUM(amount), 0) AS total_expense FROM expenses WHERE user_id = :user_id';
$totalStmt = $pdo->prepare($totalSql);
$totalStmt->execute(['user_id' => $userId]);
$totalExpense = (float) $totalStmt->fetch()['total_expense'];

$monthSql = 'SELECT COALESCE(SUM(amount), 0) AS month_expense FROM expenses WHERE user_id = :user_id AND DATE_FORMAT(date, "%Y-%m") = :month';
$monthStmt = $pdo->prepare($monthSql);
$monthStmt->execute(['user_id' => $userId, 'month' => $currentMonth]);
$monthExpense = (float) $monthStmt->fetch()['month_expense'];

$budgetSql = 'SELECT monthly_limit FROM budget WHERE user_id = :user_id LIMIT 1';
$budgetStmt = $pdo->prepare($budgetSql);
$budgetStmt->execute(['user_id' => $userId]);
$budgetRow = $budgetStmt->fetch();
$monthlyBudget = $budgetRow ? (float) $budgetRow['monthly_limit'] : 0;
$remainingBalance = $monthlyBudget - $monthExpense;

$recentSql = 'SELECT id, amount, category, description, date FROM expenses WHERE user_id = :user_id ORDER BY date DESC, id DESC LIMIT 5';
$recentStmt = $pdo->prepare($recentSql);
$recentStmt->execute(['user_id' => $userId]);
$recentExpenses = $recentStmt->fetchAll();

$categorySql = 'SELECT category, SUM(amount) AS total FROM expenses WHERE user_id = :user_id GROUP BY category ORDER BY total DESC';
$categoryStmt = $pdo->prepare($categorySql);
$categoryStmt->execute(['user_id' => $userId]);
$categoryRows = $categoryStmt->fetchAll();

$barSql = 'SELECT DATE_FORMAT(date, "%b %Y") AS month_name, SUM(amount) AS total
           FROM expenses
           WHERE user_id = :user_id
           GROUP BY DATE_FORMAT(date, "%Y-%m")
           ORDER BY DATE_FORMAT(date, "%Y-%m") ASC';
$barStmt = $pdo->prepare($barSql);
$barStmt->execute(['user_id' => $userId]);
$barRows = $barStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="mb-0">Dashboard</h2>
    <a href="expense_add.php" class="btn btn-success">+ Add Expense</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Total Expenses</p>
                <h4 class="mb-0">₹ <?= number_format($totalExpense, 2) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">This Month</p>
                <h4 class="mb-0">₹ <?= number_format($monthExpense, 2) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card shadow-sm <?= ($monthlyBudget > 0 && $remainingBalance < 0) ? 'budget-alert' : '' ?>">
            <div class="card-body">
                <p class="text-muted mb-1">Remaining Budget</p>
                <h4 class="mb-0">₹ <?= number_format($remainingBalance, 2) ?></h4>
                <?php if ($monthlyBudget <= 0): ?>
                    <small class="text-muted">Set your monthly budget to track balance.</small>
                <?php elseif ($remainingBalance < 0): ?>
                    <small class="text-danger fw-semibold">Budget exceeded this month!</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Transactions</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$recentExpenses): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">No expenses found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentExpenses as $expense): ?>
                            <tr>
                                <td><?= e($expense['date']) ?></td>
                                <td><?= e($expense['category']) ?></td>
                                <td><?= e($expense['description']) ?></td>
                                <td class="text-end">₹ <?= number_format((float) $expense['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Category Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="250"></canvas>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Monthly Spending Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
window.expenseChartData = {
    categoryLabels: <?= json_encode(array_column($categoryRows, 'category')) ?>,
    categoryValues: <?= json_encode(array_map('floatval', array_column($categoryRows, 'total'))) ?>,
    monthLabels: <?= json_encode(array_column($barRows, 'month_name')) ?>,
    monthValues: <?= json_encode(array_map('floatval', array_column($barRows, 'total'))) ?>
};
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
