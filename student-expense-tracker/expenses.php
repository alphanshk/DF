<?php
require 'includes/auth.php';
requireLogin();
require 'config/db.php';
require 'includes/functions.php';

$studentId = $_SESSION['student_id'];
$categories = getCategories();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM expenses WHERE id = ? AND student_id = ?');
        $stmt->execute([$id, $studentId]);
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $category = cleanInput($_POST['category'] ?? '');
        $description = cleanInput($_POST['description'] ?? '');
        $date = cleanInput($_POST['date'] ?? '');

        if ($amount <= 0 || $category === '' || $date === '') {
            $error = 'Amount, category and date are required.';
        } elseif (!in_array($category, $categories, true)) {
            $error = 'Invalid category selected.';
        } else {
            if ($action === 'edit' && $id > 0) {
                $stmt = $pdo->prepare('UPDATE expenses SET amount = ?, category = ?, description = ?, date = ? WHERE id = ? AND student_id = ?');
                $stmt->execute([$amount, $category, $description, $date, $id, $studentId]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO expenses (student_id, amount, category, description, date) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$studentId, $amount, $category, $description, $date]);
            }
        }
    }

    if (!$error) {
        header('Location: expenses.php');
        exit;
    }
}

$editExpense = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM expenses WHERE id = ? AND student_id = ?');
    $stmt->execute([$editId, $studentId]);
    $editExpense = $stmt->fetch();
}

$listStmt = $pdo->prepare('SELECT * FROM expenses WHERE student_id = ? ORDER BY date DESC, id DESC');
$listStmt->execute([$studentId]);
$expenses = $listStmt->fetchAll();

include 'includes/header.php';
?>

<h1>Manage Expenses</h1>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error); ?></p><?php endif; ?>

<section class="panel">
    <h2><?= $editExpense ? 'Edit Expense' : 'Quick Add Expense'; ?></h2>
    <form method="post" class="grid-form">
        <input type="hidden" name="action" value="<?= $editExpense ? 'edit' : 'add'; ?>">
        <input type="hidden" name="id" value="<?= (int)($editExpense['id'] ?? 0); ?>">

        <label>Amount</label>
        <input type="number" step="0.01" min="0" name="amount" required value="<?= htmlspecialchars($editExpense['amount'] ?? ''); ?>">

        <label>Category</label>
        <select name="category" required>
            <option value="">Select</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category); ?>" <?= (($editExpense['category'] ?? '') === $category) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($category); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Description</label>
        <input type="text" name="description" value="<?= htmlspecialchars($editExpense['description'] ?? ''); ?>">

        <label>Date</label>
        <input type="date" name="date" required value="<?= htmlspecialchars($editExpense['date'] ?? date('Y-m-d')); ?>">

        <button type="submit"><?= $editExpense ? 'Update Expense' : 'Add Expense'; ?></button>
    </form>
</section>

<section class="panel">
    <h2>All Expenses</h2>
    <table>
        <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($expenses as $expense): ?>
            <tr>
                <td><?= htmlspecialchars($expense['date']); ?></td>
                <td><?= htmlspecialchars($expense['category']); ?></td>
                <td><?= htmlspecialchars($expense['description']); ?></td>
                <td><?= formatAmount((float)$expense['amount']); ?></td>
                <td>
                    <a href="expenses.php?edit=<?= (int)$expense['id']; ?>">Edit</a>
                    <form method="post" class="inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$expense['id']; ?>">
                        <button type="submit" onclick="return confirm('Delete this expense?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php include 'includes/footer.php'; ?>
