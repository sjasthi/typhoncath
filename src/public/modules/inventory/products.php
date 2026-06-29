<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Permissions;
use App\Modules\Inventory\InventoryController;

Auth::requireLogin();

$controller = new InventoryController();
$page       = $_GET['page'] ?? 'list';
$isPost     = $_SERVER['REQUEST_METHOD'] === 'POST';

// ── Route ──────────────────────────────────────────────────────────────────
if ($page === 'detail') {
    if ($isPost) {
        Permissions::require(!empty($_POST['id']) ? 'inventory.edit' : 'inventory.create');
        $controller->save();
        exit;
    }
    Permissions::require('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->show();

} elseif ($page === 'stock') {
    if ($isPost) {
        Permissions::require('inventory.update_stock');
        $controller->updateStock();
        exit;
    }
    Permissions::require('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->editStock();

} elseif ($page === 'reservations') {
    if ($isPost) {
        Permissions::require('inventory.reserve');
        $controller->updateReservation();
        exit;
    }
    Permissions::require('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->reservations();

} elseif ($page === 'delete') {
    Permissions::require('inventory.edit');
    if ($isPost) {
        $controller->handleDelete();
        exit;
    }
    // GET with ?page=delete&id=X shows a confirmation page
    Permissions::require('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->confirmDelete();

} else {
    // Default: product list
    Permissions::require('inventory.view');
    include __DIR__ . '/../../../app/Shared/header.php';
    include __DIR__ . '/../../../app/Shared/sidebar.php';
    $controller->index();
}

include __DIR__ . '/../../../app/Shared/footer.php';
