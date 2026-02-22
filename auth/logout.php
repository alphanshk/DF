<?php
require_once __DIR__ . '/../config/helpers.php';
$_SESSION = [];
session_destroy();
session_start();
set_flash('success', 'Logged out successfully.');
redirect('/auth/login.php');
