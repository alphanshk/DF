<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
if (!empty($_SESSION['user_id'])) redirect('dashboard.php');

$name=''; $email='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name=trim($_POST['name']??'');
  $email=trim($_POST['email']??'');
  $password=$_POST['password']??'';
  if ($name===''||$email===''||$password==='') flash('error','All fields required.');
  elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) flash('error','Invalid email.');
  else {
    $s=$pdo->prepare('SELECT id FROM users WHERE email=:email LIMIT 1');
    $s->execute(['email'=>$email]);
    if ($s->fetch()) flash('error','Email already used.');
    else {
      $hash=password_hash($password,PASSWORD_BCRYPT);
      $i=$pdo->prepare('INSERT INTO users(name,email,password) VALUES(:name,:email,:password)');
      $i->execute(['name'=>$name,'email'=>$email,'password'=>$hash]);
      flash('success','Registration successful.');
      redirect('login.php');
    }
  }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-5"><div class="card"><div class="card-body">
<h4>Register</h4>
<form method="post">
<div class="mb-2"><label>Name</label><input class="form-control" name="name" value="<?= e($name) ?>" required></div>
<div class="mb-2"><label>Email</label><input type="email" class="form-control" name="email" value="<?= e($email) ?>" required></div>
<div class="mb-2"><label>Password</label><input type="password" class="form-control" name="password" required></div>
<button class="btn btn-primary w-100">Create Account</button>
</form>
<p class="mt-2 mb-0">Already have account? <a href="login.php">Login</a></p>
</div></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
