<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

redirect('login.php');
