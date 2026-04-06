<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
ensure_logged_in();

$userId = current_user_id();
$budgetAmount = '';

$currentSql = 'SELECT monthly_limit FROM budget WHERE user_id = :user_id LIMIT 1';
$currentStmt = $pdo->prepare($currentSql);
$currentStmt->execute(['user_id' => $userId]);
$currentBudget = $currentStmt->fetch();
if ($currentBudget) {
    $budgetAmount = (string) $currentBudget['monthly_limit'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $budgetAmount = trim($_POST['monthly_limit'] ?? '');

    if ($budgetAmount === '' || !is_numeric($budgetAmount) || (float) $budgetAmount <= 0) {
        set_flash('error', 'Please enter a valid monthly budget amount.');
    } else {
        if ($currentBudget) {
            $sql = 'UPDATE budget SET monthly_limit = :monthly_limit WHERE user_id = :user_id';
        } else {
            $sql = 'INSERT INTO budget (user_id, monthly_limit) VALUES (:user_id, :monthly_limit)';
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'monthly_limit' => (float) $budgetAmount,
        ]);

        set_flash('success', 'Monthly budget saved successfully.');
        redirect('budget.php');
    }
}

$currentMonth = date('Y-m');
$monthSql = 'SELECT COALESCE(SUM(amount), 0) AS month_expense
             FROM expenses
             WHERE user_id = :user_id AND DATE_FORMAT(date, "%Y-%m") = :month';
$monthStmt = $pdo->prepare($monthSql);
$monthStmt->execute(['user_id' => $userId, 'month' => $currentMonth]);
$monthExpense = (float) $monthStmt->fetch()['month_expense'];

$monthlyLimit = $budgetAmount !== '' ? (float) $budgetAmount : 0.0;
$remaining = $monthlyLimit - $monthExpense;

require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="mb-3">Set Monthly Budget</h3>
                <form method="post">
                    <label class="form-label">Monthly Budget (₹)</label>
                    <input type="number" step="0.01" min="1" name="monthly_limit" class="form-control" value="<?= e($budgetAmount) ?>" required>
                    <button type="submit" class="btn btn-primary mt-3">Save Budget</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm <?= ($monthlyLimit > 0 && $remaining < 0) ? 'budget-alert' : '' ?>">
            <div class="card-body p-4">
                <h4 class="mb-3">Current Month Budget Status</h4>
                <p><strong>Budget:</strong> ₹ <?= number_format($monthlyLimit, 2) ?></p>
                <p><strong>Spent:</strong> ₹ <?= number_format($monthExpense, 2) ?></p>
                <p><strong>Remaining:</strong> ₹ <?= number_format($remaining, 2) ?></p>
                <?php if ($monthlyLimit > 0 && $remaining < 0): ?>
                    <div class="alert alert-danger mb-0">Alert: Budget exceeded!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
