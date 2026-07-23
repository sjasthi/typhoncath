<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Dashboard\DashboardController;

Auth::requireLogin();

layout_open();

(new DashboardController())->index();

layout_close();
