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
    match ($_POST['_action'] ?? '') {
        'stage'                     => $controller->handleUpdateStagePost($id),
        'delete'                    => $controller->handleDeletePost($id),
        'delete_quote'              => $controller->handleDeleteQuotePost((int)($_POST['quote_id'] ?? 0)),
        'update_reservation_status' => $controller->handleUpdateReservationStatusPost((int)($_POST['reservation_id'] ?? 0)),
        'delete_reservation'        => $controller->handleDeleteReservationPost((int)($_POST['reservation_id'] ?? 0)),
        default                     => null,
    };
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

$controller->show($id);

include __DIR__ . '/../../../app/Shared/footer.php';
