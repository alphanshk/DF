<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
ensure_logged_in();

$categories = ['Food', 'Travel', 'Bills', 'Shopping', 'Health', 'Entertainment', 'Education', 'Other'];
$id = (int) ($_GET['id'] ?? 0);

$sql = 'SELECT id, amount, category, description, date FROM expenses WHERE id = :id AND user_id = :user_id LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id, 'user_id' => current_user_id()]);
$expense = $stmt->fetch();

if (!$expense) {
    set_flash('error', 'Expense record not found.');
    redirect('expenses.php');
}

$amount = (string) $expense['amount'];
$category = $expense['category'];
$description = $expense['description'];
$date = $expense['date'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = trim($_POST['amount'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');

    if ($amount === '' || $category === '' || $description === '' || $date === '') {
        set_flash('error', 'All fields are required.');
    } elseif (!is_numeric($amount) || (float) $amount <= 0) {
        set_flash('error', 'Amount must be a valid positive number.');
    } elseif (!in_array($category, $categories, true)) {
        set_flash('error', 'Invalid category selected.');
    } else {
        $updateSql = 'UPDATE expenses
                      SET amount = :amount, category = :category, description = :description, date = :date
                      WHERE id = :id AND user_id = :user_id';
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            'amount' => (float) $amount,
            'category' => $category,
            'description' => $description,
            'date' => $date,
            'id' => $id,
            'user_id' => current_user_id(),
        ]);

        set_flash('success', 'Expense updated successfully.');
        redirect('expenses.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="mb-3">Edit Expense</h3>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="<?= e($amount) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <?php foreach ($categories as $item): ?>
                                    <option value="<?= e($item) ?>" <?= $category === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" name="description" value="<?= e($description) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" value="<?= e($date) ?>" required>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Update Expense</button>
                        <a href="expenses.php" class="btn btn-outline-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
