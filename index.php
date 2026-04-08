<?php
require_once __DIR__ . '/config/helpers.php';
if (!current_user()) {
    redirect('/auth/login.php');
}
redirect('/user/home.php');
