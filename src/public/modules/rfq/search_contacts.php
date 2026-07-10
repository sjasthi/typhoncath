<?php
// JSON autocomplete endpoint: search contacts (optionally scoped to an account),
// or resolve one by id.
//   GET ?q=smith&account_id=5 -> [{id, account_id, label}, ...]  (capped)
//   GET ?id=12                -> [{id, account_id, label}]        (label lookup)
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

$repo      = new RFQRepository();
$q         = trim($_GET['q'] ?? '');
$id        = isset($_GET['id']) && $_GET['id'] !== '' ? (int) $_GET['id'] : null;
$accountId = isset($_GET['account_id']) && $_GET['account_id'] !== '' ? (int) $_GET['account_id'] : null;

$rows = $repo->searchContacts($q, $accountId, $id);

echo json_encode(array_map(static function ($c) {
    $name  = trim($c['first_name'] . ' ' . $c['last_name']);
    $label = $name . ($c['title'] ? ' — ' . $c['title'] : '');
    return [
        'id'         => (int) $c['id'],
        'account_id' => $c['account_id'] !== null ? (int) $c['account_id'] : null,
        'label'      => $label,
    ];
}, $rows));
