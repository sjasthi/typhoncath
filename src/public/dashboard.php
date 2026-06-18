<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();

include __DIR__ . '/../app/Shared/header.php';
include __DIR__ . '/../app/Shared/sidebar.php';
include __DIR__ . '/../app/Modules/Dashboard/views/dashboard.php';
include __DIR__ . '/../app/Shared/footer.php';
