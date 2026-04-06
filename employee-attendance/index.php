<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
redirect(!empty($_SESSION['user_id']) ? 'dashboard.php' : 'login.php');
