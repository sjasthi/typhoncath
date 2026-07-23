<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Permissions;
use App\Modules\Admin\AdminController;

Auth::requireLogin();

// Reject state-changing (POST) requests without a valid CSRF token.
require_once __DIR__ . '/../../app/Middleware/csrf.php';

if (!Permissions::can('admin.manage_users')) {
    http_response_code(403);
    include __DIR__ . '/../../app/Shared/header.php';
    include __DIR__ . '/../../app/Shared/sidebar.php';
    include __DIR__ . '/../../app/Shared/error_403.php';
    include __DIR__ . '/../../app/Shared/footer.php';
    exit;
}

$controller = new AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    match ($action) {
        'create'             => $controller->handleCreateUserPost(),
        'edit'               => $controller->handleEditUserPost($userId),
        'delete'             => $controller->handleDeleteUserPost($userId),
        'create_custom_role' => $controller->handleCreateCustomRolePost(),
        'remove_custom_role' => $controller->handleRemoveCustomRolePost(),
        default              => null,
    };
}

include __DIR__ . '/../../app/Shared/header.php';
include __DIR__ . '/../../app/Shared/sidebar.php';
$controller->dispatch();
include __DIR__ . '/../../app/Shared/footer.php';
