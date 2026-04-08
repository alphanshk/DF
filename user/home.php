<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$user = current_user();
$userId = (int) $user['id'];

$selectedMonth = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
    $selectedMonth = date('Y-m');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!verify_csrf($token)) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect('/user/home.php?month=' . urlencode($selectedMonth));
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_expense') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $amount = (float) ($_POST['amount'] ?? 0);
        $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');

        if ($title === '' || $amount <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expenseDate)) {
            set_flash('danger', 'Please provide a valid title, amount, and date.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO expenses (user_id,title,category,amount,expense_date,created_at) VALUES (?,?,?,?,?,NOW())');
            $stmt->execute([$userId, $title, $category !== '' ? $category : 'General', $amount, $expenseDate]);
            set_flash('success', 'Expense added successfully.');
        }

        redirect('/user/home.php?month=' . urlencode($selectedMonth));
    }

    if ($action === 'delete_expense') {
        $expenseId = (int) ($_POST['expense_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM expenses WHERE id = ? AND user_id = ?');
        $stmt->execute([$expenseId, $userId]);
        set_flash('success', 'Expense deleted.');
        redirect('/user/home.php?month=' . urlencode($selectedMonth));
    }
}

$allTotalsStmt = $pdo->prepare('SELECT COUNT(*) AS total_entries, COALESCE(SUM(amount), 0) AS total_spent FROM expenses WHERE user_id = ?');
$allTotalsStmt->execute([$userId]);
$allTotals = $allTotalsStmt->fetch();

$monthTotalsStmt = $pdo->prepare('SELECT COUNT(*) AS month_entries, COALESCE(SUM(amount), 0) AS month_spent FROM expenses WHERE user_id = ? AND DATE_FORMAT(expense_date, "%Y-%m") = ?');
$monthTotalsStmt->execute([$userId, $selectedMonth]);
$monthTotals = $monthTotalsStmt->fetch();

$categoryStmt = $pdo->prepare('SELECT category, COALESCE(SUM(amount), 0) AS total_amount FROM expenses WHERE user_id = ? AND DATE_FORMAT(expense_date, "%Y-%m") = ? GROUP BY category ORDER BY total_amount DESC');
$categoryStmt->execute([$userId, $selectedMonth]);
$categoryBreakdown = $categoryStmt->fetchAll();

$expensesStmt = $pdo->prepare('SELECT id,title,category,amount,expense_date FROM expenses WHERE user_id = ? AND DATE_FORMAT(expense_date, "%Y-%m") = ? ORDER BY expense_date DESC, id DESC LIMIT 50');
$expensesStmt->execute([$userId, $selectedMonth]);
$expenses = $expensesStmt->fetchAll();

$monthEntries = (int) ($monthTotals['month_entries'] ?? 0);
$monthSpent = (float) ($monthTotals['month_spent'] ?? 0);
$avgPerEntry = $monthEntries > 0 ? ($monthSpent / $monthEntries) : 0;

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="mb-1">Monthly Expense Tracker</h4>
        <p class="text-muted mb-0">Viewing: <strong><?= e(date('F Y', strtotime($selectedMonth . '-01'))) ?></strong></p>
    </div>
    <form method="get" class="d-flex gap-2 align-items-center">
        <label for="month" class="form-label mb-0">Month</label>
        <input type="month" id="month" name="month" class="form-control" value="<?= e($selectedMonth) ?>">
        <button class="btn btn-outline-primary">Apply</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card summary-card shadow-sm p-3">
            <p class="text-muted mb-1">All Entries</p>
            <p class="summary-value mb-0"><?= (int) ($allTotals['total_entries'] ?? 0) ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card shadow-sm p-3">
            <p class="text-muted mb-1">All-time Spend</p>
            <p class="summary-value mb-0">$<?= number_format((float) ($allTotals['total_spent'] ?? 0), 2) ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card shadow-sm p-3">
            <p class="text-muted mb-1">Month Spend</p>
            <p class="summary-value mb-0">$<?= number_format($monthSpent, 2) ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card shadow-sm p-3">
            <p class="text-muted mb-1">Avg / Entry</p>
            <p class="summary-value mb-0">$<?= number_format($avgPerEntry, 2) ?></p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card tracker-card shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">Add Expense</h5>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="add_expense">

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Groceries" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="Food">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" min="0.01" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="expense_date" class="form-control" value="<?= e(date('Y-m-d')) ?>" required>
                    </div>

                    <button class="btn btn-primary w-100">Add Expense</button>
                </form>
            </div>
        </div>

        <div class="card tracker-card shadow-sm mt-4">
            <div class="card-body p-4">
                <h5 class="mb-3">Category Breakdown</h5>
                <?php if (!$categoryBreakdown): ?>
                    <p class="text-muted mb-0">No expenses in this month.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($categoryBreakdown as $cat): ?>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><?= e($cat['category']) ?></span>
                                <strong>$<?= number_format((float) $cat['total_amount'], 2) ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card tracker-card shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Expenses for <?= e(date('F Y', strtotime($selectedMonth . '-01'))) ?></h5>
                    <span class="text-muted small">Latest 50 entries</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!$expenses): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No expenses added for this month.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?= e($expense['title']) ?></td>
                                <td><?= e($expense['category']) ?></td>
                                <td><?= e($expense['expense_date']) ?></td>
                                <td class="text-end fw-semibold">$<?= number_format((float) $expense['amount'], 2) ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_expense">
                                        <input type="hidden" name="expense_id" value="<?= (int) $expense['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
