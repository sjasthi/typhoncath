<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Campaign\CampaignController;

Auth::requireLogin();

$campaignId = (int)($_GET['campaign_id'] ?? 0);
if ($campaignId === 0) {
    header('Location: /modules/campaign/campaigns.php');
    exit;
}

$controller = new CampaignController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    match ($action) {
        'remove_segment' => (function () use ($controller, $campaignId) {
            $segmentName = trim($_POST['segment_name'] ?? '');
            if ($segmentName !== '') $controller->handleRemoveSegment($segmentName, $campaignId);
        })(),
        'save_preset'    => $controller->handleSavePresetPost($campaignId),
        'apply_preset'   => $controller->handleApplyPresetPost($campaignId),
        default          => $controller->handleAudiencePost($campaignId),
    };
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
$controller->audience($campaignId);
include __DIR__ . '/../../../app/Shared/footer.php';
