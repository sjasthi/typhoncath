<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\RFQ\RFQController;

Auth::requireLogin();

// Reject state-changing (POST) requests without a valid CSRF token.
require_once __DIR__ . '/../../../app/Middleware/csrf.php';


$quoteId = (int)($_GET['id'] ?? 0);
if ($quoteId === 0) {
    header('Location: /modules/rfq/pipeline.php');
    exit;
}

$controller = new RFQController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleEditQuotePost($quoteId); // redirects + exits on success
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
$controller->editQuote($quoteId);
include __DIR__ . '/../../../app/Shared/footer.php';
