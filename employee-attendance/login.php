<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
if (!empty($_SESSION['user_id'])) redirect('dashboard.php');

$email='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email=trim($_POST['email']??'');
  $password=$_POST['password']??'';
  $s=$pdo->prepare('SELECT id,name,password FROM users WHERE email=:email LIMIT 1');
  $s->execute(['email'=>$email]);
  $user=$s->fetch();
  if (!$user || !password_verify($password,$user['password'])) flash('error','Invalid credentials.');
  else {
    $_SESSION['user_id']=(int)$user['id'];
    $_SESSION['user_name']=$user['name'];
    redirect('dashboard.php');
  }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-5"><div class="card"><div class="card-body">
<h4>Login</h4>
<form method="post">
<div class="mb-2"><label>Email</label><input type="email" class="form-control" name="email" value="<?= e($email) ?>" required></div>
<div class="mb-2"><label>Password</label><input type="password" class="form-control" name="password" required></div>
<button class="btn btn-dark w-100">Login</button>
</form>
<p class="mt-2 mb-0">New user? <a href="register.php">Register</a></p>
</div></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
