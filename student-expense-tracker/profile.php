<?php
require 'includes/auth.php';
requireLogin();
require 'config/db.php';
require 'includes/functions.php';

$studentId = $_SESSION['student_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name'] ?? '');
    $college = cleanInput($_POST['college_name'] ?? '');
    $limit = (float)($_POST['monthly_limit'] ?? 0);

    if ($name === '') {
        $error = 'Name cannot be empty.';
    } else {
        $updateProfile = $pdo->prepare('UPDATE students SET name = ?, college_name = ? WHERE id = ?');
        $updateProfile->execute([$name, $college, $studentId]);
        $_SESSION['student_name'] = $name;

        $checkAllowance = $pdo->prepare('SELECT id FROM allowance WHERE student_id = ?');
        $checkAllowance->execute([$studentId]);

        if ($checkAllowance->fetch()) {
            $allowanceStmt = $pdo->prepare('UPDATE allowance SET monthly_limit = ? WHERE student_id = ?');
            $allowanceStmt->execute([$limit, $studentId]);
        } else {
            $allowanceStmt = $pdo->prepare('INSERT INTO allowance (student_id, monthly_limit) VALUES (?, ?)');
            $allowanceStmt->execute([$studentId, $limit]);
        }

        $message = 'Profile and allowance updated successfully.';
    }
}

$studentStmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$studentStmt->execute([$studentId]);
$student = $studentStmt->fetch();

$allowanceStmt = $pdo->prepare('SELECT monthly_limit FROM allowance WHERE student_id = ?');
$allowanceStmt->execute([$studentId]);
$allowance = $allowanceStmt->fetch();

include 'includes/header.php';
?>

<h1>Profile</h1>
<?php if ($message): ?><p class="success"><?= htmlspecialchars($message); ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error); ?></p><?php endif; ?>

<section class="panel">
    <form method="post" class="grid-form">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($student['name'] ?? ''); ?>" required>

        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($student['email'] ?? ''); ?>" disabled>

        <label>College Name</label>
        <input type="text" name="college_name" value="<?= htmlspecialchars($student['college_name'] ?? ''); ?>">

        <label>Monthly Allowance</label>
        <input type="number" step="0.01" min="0" name="monthly_limit" value="<?= htmlspecialchars($allowance['monthly_limit'] ?? '0'); ?>">

        <button type="submit">Save Changes</button>
    </form>
</section>

<?php include 'includes/footer.php'; ?>
