<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Campaign\CampaignRepository;

Auth::requireLogin();

// Reject state-changing (POST) requests without a valid CSRF token.
require_once __DIR__ . '/../../../app/Middleware/csrf.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST required']);
    exit;
}

$tagFilter  = trim($_POST['tag_filter']  ?? '');
$accountIds = array_filter(array_map('intval', (array)($_POST['account_ids'] ?? [])));
$contactIds = array_filter(array_map('intval', (array)($_POST['contact_ids'] ?? [])));

$repo   = new CampaignRepository();
$counts = $repo->previewAudienceCount($tagFilter, array_values($accountIds), array_values($contactIds));

echo json_encode($counts);
