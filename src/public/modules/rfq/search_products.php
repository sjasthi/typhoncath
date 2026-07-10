<?php
// JSON autocomplete endpoint: search products by name/SKU, or resolve one by id.
//   GET ?q=widget -> [{id, label, price, available_quantity, ...}, ...]  (capped)
//   GET ?id=7     -> [{...}]                                             (label lookup)
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

$rows = $repo->searchProducts($q, $id);

echo json_encode(array_map(static fn($p) => [
    'id'                 => (int) $p['id'],
    'label'              => $p['product_name'] . ' (' . $p['sku'] . ')',
    'product_name'       => $p['product_name'],
    'sku'                => $p['sku'],
    'price'              => (float) $p['price'],
    'available_quantity' => (int) $p['available_quantity'],
], $rows));
