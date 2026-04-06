<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
ensure_logged_in();

$userId = current_user_id();

$sql = 'SELECT name, email, created_at FROM users WHERE id = :id LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'User profile not found.');
    redirect('logout.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="mb-3">Profile</h3>
                <p><strong>Name:</strong> <?= e($user['name']) ?></p>
                <p><strong>Email:</strong> <?= e($user['email']) ?></p>
                <p><strong>Member Since:</strong> <?= e(date('d M Y', strtotime($user['created_at']))) ?></p>
                <a href="dashboard.php" class="btn btn-outline-primary mt-2">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
