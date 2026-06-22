<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\RFQ\RFQController;

Auth::requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: /modules/rfq/pipeline.php');
    exit;
}

$controller = new RFQController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleUpdatePost($id); // redirects + exits on success
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

$controller->edit($id);

include __DIR__ . '/../../../app/Shared/footer.php';
