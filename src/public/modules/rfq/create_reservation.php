<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\RFQ\RFQController;

Auth::requireLogin();

$rfqId = (int)($_GET['rfq_id'] ?? $_POST['rfq_id'] ?? 0);

if ($rfqId === 0) {
    header('Location: /modules/rfq/pipeline.php');
    exit;
}

$controller = new RFQController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleCreateReservationPost(); // redirects + exits on success
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

$controller->createReservation($rfqId);

include __DIR__ . '/../../../app/Shared/footer.php';
