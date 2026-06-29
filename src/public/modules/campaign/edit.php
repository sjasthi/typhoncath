<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Campaign\CampaignController;

Auth::requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    header('Location: /modules/campaign/campaigns.php');
    exit;
}

$controller = new CampaignController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleUpdatePost($id); // redirects + exits on success
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
$controller->edit($id);
include __DIR__ . '/../../../app/Shared/footer.php';
