<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Permissions;
use App\Modules\RFQ\RFQController;

Auth::requireLogin();
if (!Permissions::can('rfqs.edit')) {
    http_response_code(403);
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    include __DIR__ . '/../../../app/Shared/error_403.php';
    include __DIR__ . '/../../../app/Shared/footer.php';
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: /modules/rfq/pipeline.php');
    exit;
}

$controller = new RFQController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    match ($_POST['_action'] ?? '') {
        'delete_reservation' => $controller->handleDeleteReservationPost((int)($_POST['reservation_id'] ?? 0)),
        default              => $controller->handleUpdatePost($id),
    };
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

$controller->edit($id);

include __DIR__ . '/../../../app/Shared/footer.php';
