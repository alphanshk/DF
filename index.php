<?php
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    role_redirect($_SESSION['user']['role']);
}

redirect('auth/login.php');
