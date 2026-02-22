<?php
require_once __DIR__ . '/config/helpers.php';
if (!current_user()) {
    redirect('/auth/login.php');
}
$role = current_user()['role'];
if ($role === 'admin') redirect('/admin/dashboard.php');
if ($role === 'seller') redirect('/seller/dashboard.php');
redirect('/user/home.php');
