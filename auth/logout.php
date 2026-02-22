<?php
require_once __DIR__ . '/../includes/auth.php';

$_SESSION = [];
session_destroy();
session_start();
set_flash('success', 'Logged out successfully.');
redirect_to('/auth/login.php');
