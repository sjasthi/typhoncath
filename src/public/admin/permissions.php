<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Admin\AdminController;

Auth::requireLogin();

// Reject state-changing (POST) requests without a valid CSRF token.
require_once __DIR__ . '/../../app/Middleware/csrf.php';

$controller = new AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleSaveMatrixPost(); // redirects + exits on success
}

layout_open();
$controller->permissionMatrix();
layout_close();
