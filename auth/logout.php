<?php
require_once __DIR__ . '/../config/helpers.php';

session_unset();
session_destroy();
session_start();
set_flash('success', 'You have been logged out.');
redirect('auth/login.php');
