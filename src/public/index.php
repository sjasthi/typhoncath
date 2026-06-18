<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

header('Location: /dashboard.php');
exit;
