<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\RFQ\RFQController;
use App\Core\Permissions;

Auth::requireLogin();

// Reject state-changing (POST) requests without a valid CSRF token.
require_once __DIR__ . '/../../../app/Middleware/csrf.php';
if (!Permissions::can('rfqs.create')) {
    http_response_code(403);
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    include __DIR__ . '/../../../app/Shared/error_403.php';
    include __DIR__ . '/../../../app/Shared/footer.php';
    exit;
}

$controller = new RFQController();

// Process POST before any output so the redirect header can be sent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleCreatePost(); // redirects + exits on success
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

$controller->create();

include __DIR__ . '/../../../app/Shared/footer.php';
