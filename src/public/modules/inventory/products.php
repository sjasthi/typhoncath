<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Permissions;
use App\Modules\Inventory\InventoryController;

Auth::requireLogin();

// Shows the shared styled 403 page (same one the RFQ module uses) and halts
// the request when the current user lacks the given permission.
function denyUnlessAllowed(string $permission): void
{
    if (!Permissions::can($permission)) {
        http_response_code(403);
        include __DIR__ . '/../../../app/Shared/header.php';
        include __DIR__ . '/../../../app/Shared/sidebar.php';
        include __DIR__ . '/../../../app/Shared/error_403.php';
        include __DIR__ . '/../../../app/Shared/footer.php';
        exit;
    }
}

$controller = new InventoryController();
$page       = $_GET['page'] ?? 'list';
$isPost     = $_SERVER['REQUEST_METHOD'] === 'POST';

// ── Route ──────────────────────────────────────────────────────────────────
if ($page === 'detail') {
    if ($isPost) {
        denyUnlessAllowed(!empty($_POST['id']) ? 'inventory.edit' : 'inventory.create');
        $controller->save();
        exit;
    }
    denyUnlessAllowed('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->show();

} elseif ($page === 'stock') {
    if ($isPost) {
        denyUnlessAllowed('inventory.update_stock');
        $controller->updateStock();
        exit;
    }
    denyUnlessAllowed('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->editStock();

} elseif ($page === 'reservations') {
    if ($isPost) {
        denyUnlessAllowed('inventory.reserve');
        $controller->updateReservation();
        exit;
    }
    denyUnlessAllowed('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->reservations();

} elseif ($page === 'delete') {
    denyUnlessAllowed('inventory.edit');
    if ($isPost) {
        $controller->handleDelete();
        exit;
    }
    // GET with ?page=delete&id=X shows a confirmation page
    denyUnlessAllowed('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->confirmDelete();

} else {
    // Default: product list
    denyUnlessAllowed('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->index();
}

include __DIR__ . '/../../../app/Shared/footer.php';
