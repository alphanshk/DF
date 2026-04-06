<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
ensure_logged_in();

$userId = current_user_id();
$categoryFilter = trim($_GET['category'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT id, amount, category, description, date FROM expenses WHERE user_id = :user_id';
$params = ['user_id' => $userId];

if ($categoryFilter !== '') {
    $sql .= ' AND category = :category';
    $params['category'] = $categoryFilter;
}

if ($startDate !== '') {
    $sql .= ' AND date >= :start_date';
    $params['start_date'] = $startDate;
}

if ($endDate !== '') {
    $sql .= ' AND date <= :end_date';
    $params['end_date'] = $endDate;
}

if ($search !== '') {
    $sql .= ' AND (description LIKE :search OR category LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$sql .= ' ORDER BY date DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$expenses = $stmt->fetchAll();

$categories = ['Food', 'Travel', 'Bills', 'Shopping', 'Health', 'Entertainment', 'Education', 'Other'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="mb-0">Manage Expenses</h2>
    <a href="expense_add.php" class="btn btn-success">+ Add Expense</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select class="form-select" name="category">
                    <option value="">All</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>" <?= $categoryFilter === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="<?= e($startDate) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="<?= e($endDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" value="<?= e($search) ?>" placeholder="Description or category">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button class="btn btn-primary w-100" type="submit">Apply</button>
                <a class="btn btn-outline-secondary" href="expenses.php">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th class="text-end">Amount</th>
                <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$expenses): ?>
                <tr>
                    <td colspan="5" class="text-center py-4">No expense records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= e($expense['date']) ?></td>
                        <td><?= e($expense['category']) ?></td>
                        <td><?= e($expense['description']) ?></td>
                        <td class="text-end">₹ <?= number_format((float) $expense['amount'], 2) ?></td>
                        <td class="text-center">
                            <a href="expense_edit.php?id=<?= (int) $expense['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="expense_delete.php?id=<?= (int) $expense['id'] ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this expense?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
