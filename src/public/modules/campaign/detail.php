<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Campaign\CampaignController;

Auth::requireLogin();

// Reject state-changing (POST) requests without a valid CSRF token.
require_once __DIR__ . '/../../../app/Middleware/csrf.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    header('Location: /modules/campaign/campaigns.php');
    exit;
}

$controller = new CampaignController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    if ($action === 'simulate') {
        $controller->handleSimulatePost($id);
    } elseif ($action === 'delete') {
        $controller->handleDeletePost($id);
    }
}

layout_open();
$controller->show($id);
layout_close();
