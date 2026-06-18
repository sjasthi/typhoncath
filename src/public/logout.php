<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;

Auth::logout();

header('Location: /login.php');
exit;
