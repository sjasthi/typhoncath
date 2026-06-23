<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\RFQ\RFQController;

Auth::requireLogin();

$controller = new RFQController();

// Process POST before any output so the redirect header can be sent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleCreatePost(); // redirects + exits on success
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

$controller->create();

include __DIR__ . '/../../../app/Shared/footer.php';
