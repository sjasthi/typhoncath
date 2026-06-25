<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Campaign\CampaignController;

Auth::requireLogin();

$controller = new CampaignController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleCreatePost(); // redirects + exits on success
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
$controller->create();
include __DIR__ . '/../../../app/Shared/footer.php';
