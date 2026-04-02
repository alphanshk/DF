<?php
require 'includes/auth.php';
requireLogin();
require 'config/db.php';
require 'includes/functions.php';

$studentId = $_SESSION['student_id'];

$catStmt = $pdo->prepare('SELECT category, SUM(amount) AS total FROM expenses WHERE student_id = ? GROUP BY category');
$catStmt->execute([$studentId]);
$catData = $catStmt->fetchAll();

$weeklyStmt = $pdo->prepare('SELECT DATE(date) as day, SUM(amount) as total FROM expenses WHERE student_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(date) ORDER BY day ASC');
$weeklyStmt->execute([$studentId]);
$trendData = $weeklyStmt->fetchAll();

$insight = 'Start tracking expenses regularly for better insights.';
if ($catData) {
    usort($catData, fn($a, $b) => $b['total'] <=> $a['total']);
    $insight = 'You spend more on ' . $catData[0]['category'] . '.';
}
if (count($trendData) > 13) {
    $firstHalf = array_sum(array_map(fn($row) => (float)$row['total'], array_slice($trendData, 0, 7)));
    $secondHalf = array_sum(array_map(fn($row) => (float)$row['total'], array_slice($trendData, -7)));
    if ($secondHalf > $firstHalf) {
        $insight .= ' Your recent weekly spending has increased.';
    }
}

include 'includes/header.php';
?>

<h1>Reports & Insights</h1>
<section class="panel">
    <h2>Smart Insight</h2>
    <p><?= htmlspecialchars($insight); ?></p>
</section>

<div class="chart-grid">
    <section class="panel">
        <h2>Category-wise Expense (Pie Chart)</h2>
        <canvas id="categoryChart"></canvas>
    </section>

    <section class="panel">
        <h2>30-Day Spending Trend (Bar Chart)</h2>
        <canvas id="trendChart"></canvas>
    </section>
</div>

<script>
    window.categoryChartData = <?= json_encode($catData); ?>;
    window.trendChartData = <?= json_encode($trendData); ?>;
</script>
<script src="assets/js/charts.js"></script>

<?php include 'includes/footer.php'; ?>
