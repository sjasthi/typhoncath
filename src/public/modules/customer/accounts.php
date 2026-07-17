<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Customer\CustomerRepository;

Auth::requireLogin();

// List is now a client-driven DataTable (server-side processing). This page just
// renders the shell; rows come from accounts_data.php. We only need the distinct
// industry/source values to populate the per-column filter dropdowns.
$repo       = new CustomerRepository();
$industries = $repo->distinctValues('industry');
$sources    = $repo->distinctValues('source');

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
include __DIR__ . '/../../../app/Modules/Customer/views/accounts_list.php';
include __DIR__ . '/../../../app/Shared/footer.php';
