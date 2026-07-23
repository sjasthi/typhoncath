<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\RFQ\RFQController;

Auth::requireLogin();

// Reject state-changing (POST) requests without a valid CSRF token.
require_once __DIR__ . '/../../../app/Middleware/csrf.php';

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

layout_open();

$controller->show($id);

layout_close();
