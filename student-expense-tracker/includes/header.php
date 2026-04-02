<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Expense Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php if (isset($_SESSION['student_id'])): ?>
<div class="layout">
    <aside class="sidebar">
        <h2>💸 Tracker</h2>
        <a class="<?= $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
        <a class="<?= $currentPage === 'expenses.php' ? 'active' : ''; ?>" href="expenses.php">Add Expense</a>
        <a class="<?= $currentPage === 'reports.php' ? 'active' : ''; ?>" href="reports.php">Reports</a>
        <a class="<?= $currentPage === 'profile.php' ? 'active' : ''; ?>" href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
        <button id="darkModeToggle" class="dark-btn" type="button">Toggle Dark Mode</button>
    </aside>
    <main class="content">
<?php endif; ?>
