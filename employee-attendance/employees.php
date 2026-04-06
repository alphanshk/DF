<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_login();

$userId=(int)$_SESSION['user_id'];
$editId=(int)($_GET['edit']??0);
$deleteId=(int)($_GET['delete']??0);

if ($deleteId>0) {
  $d=$pdo->prepare('DELETE FROM employees WHERE id=:id AND user_id=:user_id');
  $d->execute(['id'=>$deleteId,'user_id'=>$userId]);
  flash('success','Employee deleted.');
  redirect('employees.php');
}

$name='';$email='';$department='';
if ($editId>0) {
  $e=$pdo->prepare('SELECT * FROM employees WHERE id=:id AND user_id=:user_id');
  $e->execute(['id'=>$editId,'user_id'=>$userId]);
  $row=$e->fetch();
  if ($row) { $name=$row['name'];$email=$row['email'];$department=$row['department']; }
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name=trim($_POST['name']??'');
  $email=trim($_POST['email']??'');
  $department=trim($_POST['department']??'');
  $id=(int)($_POST['id']??0);
  if ($name===''||$email===''||$department==='') flash('error','All employee fields are required.');
  else {
    if ($id>0) {
      $u=$pdo->prepare('UPDATE employees SET name=:name,email=:email,department=:department WHERE id=:id AND user_id=:user_id');
      $u->execute(['name'=>$name,'email'=>$email,'department'=>$department,'id'=>$id,'user_id'=>$userId]);
      flash('success','Employee updated.');
    } else {
      $i=$pdo->prepare('INSERT INTO employees(user_id,name,email,department) VALUES(:user_id,:name,:email,:department)');
      $i->execute(['user_id'=>$userId,'name'=>$name,'email'=>$email,'department'=>$department]);
      flash('success','Employee added.');
    }
    redirect('employees.php');
  }
}

$l=$pdo->prepare('SELECT * FROM employees WHERE user_id=:user_id ORDER BY id DESC');
$l->execute(['user_id'=>$userId]);
$employees=$l->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
<div class="col-lg-4">
  <div class="card"><div class="card-body">
    <h5><?= $editId>0 ? 'Edit' : 'Add' ?> Employee</h5>
    <form method="post">
      <input type="hidden" name="id" value="<?= $editId ?>">
      <div class="mb-2"><label>Name</label><input class="form-control" name="name" value="<?= e($name) ?>" required></div>
      <div class="mb-2"><label>Email</label><input class="form-control" type="email" name="email" value="<?= e($email) ?>" required></div>
      <div class="mb-2"><label>Department</label><input class="form-control" name="department" value="<?= e($department) ?>" required></div>
      <button class="btn btn-primary w-100"><?= $editId>0 ? 'Update' : 'Add' ?></button>
    </form>
  </div></div>
</div>
<div class="col-lg-8">
  <div class="card"><div class="card-body">
    <h5>Employee List</h5>
    <div class="table-responsive">
      <table class="table table-striped"><thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Actions</th></tr></thead><tbody>
      <?php foreach($employees as $emp): ?>
      <tr>
        <td><?= e($emp['name']) ?></td><td><?= e($emp['email']) ?></td><td><?= e($emp['department']) ?></td>
        <td>
          <a class="btn btn-sm btn-outline-primary" href="employees.php?edit=<?= (int)$emp['id'] ?>">Edit</a>
          <a class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete employee?')" href="employees.php?delete=<?= (int)$emp['id'] ?>">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody></table>
    </div>
  </div></div>
</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
