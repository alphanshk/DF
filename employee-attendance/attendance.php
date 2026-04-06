<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$userId=(int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $employeeId=(int)($_POST['employee_id']??0);
  $date=$_POST['attendance_date']??date('Y-m-d');
  $status=$_POST['status']??'Present';
  if (!in_array($status,['Present','Absent','Leave'],true)) $status='Present';

  $exists=$pdo->prepare('SELECT id FROM attendance WHERE user_id=:user_id AND employee_id=:employee_id AND attendance_date=:attendance_date');
  $exists->execute(['user_id'=>$userId,'employee_id'=>$employeeId,'attendance_date'=>$date]);
  $row=$exists->fetch();
  if ($row) {
    $u=$pdo->prepare('UPDATE attendance SET status=:status WHERE id=:id AND user_id=:user_id');
    $u->execute(['status'=>$status,'id'=>$row['id'],'user_id'=>$userId]);
    flash('success','Attendance updated.');
  } else {
    $i=$pdo->prepare('INSERT INTO attendance(user_id,employee_id,attendance_date,status) VALUES(:user_id,:employee_id,:attendance_date,:status)');
    $i->execute(['user_id'=>$userId,'employee_id'=>$employeeId,'attendance_date'=>$date,'status'=>$status]);
    flash('success','Attendance marked.');
  }
  redirect('attendance.php');
}

$employeesStmt=$pdo->prepare('SELECT id,name,department FROM employees WHERE user_id=:user_id ORDER BY name ASC');
$employeesStmt->execute(['user_id'=>$userId]);
$employees=$employeesStmt->fetchAll();

$recordsStmt=$pdo->prepare('SELECT a.attendance_date,a.status,e.name,e.department
                            FROM attendance a
                            JOIN employees e ON e.id=a.employee_id
                            WHERE a.user_id=:user_id
                            ORDER BY a.attendance_date DESC,a.id DESC LIMIT 50');
$recordsStmt->execute(['user_id'=>$userId]);
$records=$recordsStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
<div class="col-lg-4">
  <div class="card"><div class="card-body">
    <h5>Mark Attendance</h5>
    <form method="post">
      <div class="mb-2"><label>Employee</label><select class="form-select" name="employee_id" required>
        <option value="">Select</option>
        <?php foreach($employees as $emp): ?>
          <option value="<?= (int)$emp['id'] ?>"><?= e($emp['name']) ?> (<?= e($emp['department']) ?>)</option>
        <?php endforeach; ?>
      </select></div>
      <div class="mb-2"><label>Date</label><input type="date" class="form-control" name="attendance_date" value="<?= date('Y-m-d') ?>" required></div>
      <div class="mb-2"><label>Status</label><select class="form-select" name="status"><option>Present</option><option>Absent</option><option>Leave</option></select></div>
      <button class="btn btn-success w-100">Save Attendance</button>
    </form>
  </div></div>
</div>
<div class="col-lg-8">
  <div class="card"><div class="card-body">
    <h5>Recent Attendance Records</h5>
    <div class="table-responsive">
    <table class="table table-striped"><thead><tr><th>Date</th><th>Employee</th><th>Department</th><th>Status</th></tr></thead><tbody>
      <?php foreach($records as $r): ?>
      <tr><td><?= e($r['attendance_date']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['department']) ?></td><td><?= e($r['status']) ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
    </div>
  </div></div>
</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
