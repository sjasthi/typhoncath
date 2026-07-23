<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Campaign\CampaignController;
use App\Core\Permissions;

Auth::requireLogin();
if (!Permissions::can('campaigns.view')) {
    http_response_code(403);
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    include __DIR__ . '/../../../app/Shared/error_403.php';
    include __DIR__ . '/../../../app/Shared/footer.php';
    exit;
}


include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
$controller = new CampaignController();
$controller->index();
include __DIR__ . '/../../../app/Shared/footer.php';
