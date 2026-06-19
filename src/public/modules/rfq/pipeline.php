<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';


use App\Core\Auth;

use App\Modules\RFQ\RFQController;

Auth::requireLogin();

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

$controller = new RFQController();
$controller->index();

include __DIR__ . '/../../../app/Shared/footer.php';
