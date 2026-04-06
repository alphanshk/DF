<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$userId=(int)$_SESSION['user_id'];

$totalEmployees=(int)$pdo->query("SELECT COUNT(*) FROM employees WHERE user_id={$userId}")->fetchColumn();
$today=date('Y-m-d');
$todayPresent=(int)$pdo->query("SELECT COUNT(*) FROM attendance WHERE user_id={$userId} AND attendance_date='{$today}' AND status='Present'")->fetchColumn();
$todayAbsent=(int)$pdo->query("SELECT COUNT(*) FROM attendance WHERE user_id={$userId} AND attendance_date='{$today}' AND status='Absent'")->fetchColumn();

$monthly=$pdo->prepare("SELECT DATE_FORMAT(attendance_date, '%b %Y') AS m, SUM(status='Present') AS p
                        FROM attendance
                        WHERE user_id=:user_id
                        GROUP BY DATE_FORMAT(attendance_date,'%Y-%m')
                        ORDER BY DATE_FORMAT(attendance_date,'%Y-%m') ASC");
$monthly->execute(['user_id'=>$userId]);
$data=$monthly->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="card"><div class="card-body"><h6>Total Employees</h6><h3><?= $totalEmployees ?></h3></div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body"><h6>Today Present (<?= e($today) ?>)</h6><h3><?= $todayPresent ?></h3></div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body"><h6>Today Absent</h6><h3><?= $todayAbsent ?></h3></div></div></div>
</div>
<div class="card"><div class="card-body"><h5>Monthly Present Trend</h5><canvas id="attendanceChart" height="100"></canvas></div></div>
<script>
window.attendanceChartData = {
  labels: <?= json_encode(array_column($data,'m')) ?>,
  values: <?= json_encode(array_map('intval', array_column($data,'p'))) ?>
};
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
