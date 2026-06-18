<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Permissions;

Auth::requireLogin();
// Permissions::require('manage_users');

include __DIR__ . '/../../app/Shared/header.php';
include __DIR__ . '/../../app/Shared/sidebar.php';
include __DIR__ . '/../../app/Modules/Admin/views/users.php';
include __DIR__ . '/../../app/Shared/footer.php';
