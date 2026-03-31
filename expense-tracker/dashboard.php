<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/expense_functions.php';

requireLogin();

$userId = currentUserId();
$totalExpenses = getTotalExpenses($pdo, $userId);
$lastTransactions = getLastTransactions($pdo, $userId, 5);
$monthlySummary = getMonthlySummary($pdo, $userId);
$categorySummary = getCategorySummary($pdo, $userId);
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Expense Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="topbar">
    <div>
        <h1>Smart Expense Tracker</h1>
        <p>Hello, <strong><?php echo htmlspecialchars(currentUserName()); ?></strong> 👋</p>
    </div>
    <div class="topbar-actions">
        <button id="themeToggle" class="btn btn-secondary">🌙 Dark Mode</button>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</header>

<main class="container">
    <section class="cards-grid">
        <article class="card stat-card">
            <h3>Total Spending</h3>
            <p id="totalExpenseValue">$<?php echo number_format($totalExpenses, 2); ?></p>
        </article>
        <article class="card stat-card">
            <h3>This Month</h3>
            <p>
                $
                <?php
                $currentMonth = date('Y-m');
                $thisMonthTotal = 0;
                foreach ($monthlySummary as $month) {
                    if ($month['month'] === $currentMonth) {
                        $thisMonthTotal = (float) $month['total'];
                        break;
                    }
                }
                echo number_format($thisMonthTotal, 2);
                ?>
            </p>
        </article>
        <article class="card stat-card">
            <h3>Recent Transactions</h3>
            <p><?php echo count($lastTransactions); ?> (last 5)</p>
        </article>
    </section>

    <section class="card">
        <h2>Add Expense</h2>
        <form id="expenseForm" class="form-grid">
            <input type="text" name="title" placeholder="Expense title" maxlength="150" required>
            <input type="number" name="amount" placeholder="Amount" step="0.01" min="0.01" required>
            <select name="category" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
            <button class="btn btn-primary" type="submit">Add Expense</button>
        </form>
    </section>

    <section class="card">
        <h2>Search & Filter</h2>
        <form id="filterForm" class="form-grid filters">
            <input type="text" name="search" placeholder="Search by title">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="from_date">
            <input type="date" name="to_date">
            <button type="submit" class="btn btn-secondary">Apply Filters</button>
            <button type="button" id="resetFilters" class="btn">Reset</button>
            <button type="button" id="exportCsvBtn" class="btn btn-success">Export CSV</button>
        </form>
    </section>

    <section class="card">
        <h2>All Expenses</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="expenseTableBody"></tbody>
            </table>
        </div>
        <div class="pagination" id="pagination"></div>
    </section>

    <section class="cards-grid">
        <article class="card">
            <h2>Monthly Summary</h2>
            <canvas id="monthlyChart"></canvas>
        </article>
        <article class="card">
            <h2>Category-wise Spending</h2>
            <canvas id="categoryChart"></canvas>
        </article>
    </section>

    <section class="card">
        <h2>Last 5 Transactions</h2>
        <ul class="transactions-list">
            <?php if (!$lastTransactions): ?>
                <li>No transactions yet.</li>
            <?php else: ?>
                <?php foreach ($lastTransactions as $trx): ?>
                    <li>
                        <span><?php echo htmlspecialchars($trx['title']); ?> (<?php echo htmlspecialchars($trx['category']); ?>)</span>
                        <strong>$<?php echo number_format((float) $trx['amount'], 2); ?></strong>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </section>
</main>

<!-- Edit Expense Modal -->
<div id="editModal" class="modal hidden">
    <div class="modal-content">
        <h3>Edit Expense</h3>
        <form id="editExpenseForm" class="form-grid">
            <input type="hidden" name="id" id="editId">
            <input type="text" name="title" id="editTitle" required maxlength="150">
            <input type="number" name="amount" id="editAmount" required step="0.01" min="0.01">
            <select name="category" id="editCategory" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date" id="editDate" required>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" id="closeModal" class="btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="toastContainer" class="toast-container"></div>

<script>
window.__INITIAL_CHART_DATA__ = {
    monthly: <?php echo json_encode($monthlySummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
    category: <?php echo json_encode($categorySummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
};
</script>
<script src="assets/js/script.js"></script>
</body>
</html>
