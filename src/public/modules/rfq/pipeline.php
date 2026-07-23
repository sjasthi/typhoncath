<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';


use App\Core\Auth;
use App\Core\Permissions;
use App\Modules\RFQ\RFQController;

Auth::requireLogin();
if (!Permissions::can('rfqs.view')) {
    http_response_code(403);
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    include __DIR__ . '/../../../app/Shared/error_403.php';
    include __DIR__ . '/../../../app/Shared/footer.php';
    exit;
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

$controller = new RFQController();
$controller->index();


include __DIR__ . '/../../../app/Shared/footer.php';
