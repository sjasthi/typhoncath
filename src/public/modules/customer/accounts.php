<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Customer\CustomerRepository;

Auth::requireLogin();

$repo = new CustomerRepository();

// Create moved to create_account.php; delete moved to account_detail.php.
// This page is now list + search only.

$searchQ  = $_GET['search']   ?? '';
$industry = $_GET['industry'] ?? '';
$source   = $_GET['source']   ?? '';

// Shared pagination: whitelists per_page, clamps the page, yields limit()/offset().
$total    = $repo->searchCount($searchQ, $industry, $source);
$pager    = new \App\Core\Paginator($total, $_GET['per_page'] ?? 25, $_GET['page'] ?? 1);
$accounts = $repo->search($searchQ, $industry, $source, $pager->limit(), $pager->offset());

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
include __DIR__ . '/../../../app/Modules/Customer/views/accounts_list.php';
include __DIR__ . '/../../../app/Shared/footer.php';
