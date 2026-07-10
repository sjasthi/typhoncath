<?php
// JSON autocomplete endpoint: search accounts by name, or resolve one by id.
//   GET ?q=acme   -> [{id, label}, ...]   (capped)
//   GET ?id=5     -> [{id, label}]        (label lookup for a selected value)
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Permissions;
use App\Modules\RFQ\RFQRepository;

Auth::requireLogin();

header('Content-Type: application/json');

if (!Permissions::can('rfqs.view')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$repo = new RFQRepository();
$q    = trim($_GET['q'] ?? '');
$id   = isset($_GET['id']) && $_GET['id'] !== '' ? (int) $_GET['id'] : null;

$rows = $repo->searchAccounts($q, $id);

echo json_encode(array_map(static fn($a) => [
    'id'    => (int) $a['id'],
    'label' => $a['account_name'],
], $rows));
