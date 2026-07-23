<?php
/**
 * Export endpoint for the Customer accounts list (CSV / XML / PDF-via-print).
 */
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Customer\CustomerRepository;
use App\Core\DataTable\Exporter;

Auth::requireLogin();

$format = (string)($_GET['format'] ?? 'csv');
$rows   = CustomerRepository::listTable()->allRows($_GET);

$columns = [
    'account_name' => 'Name',
    'email'        => 'Email',
    'phone'        => 'Phone',
    'industry'     => 'Industry',
    'source'       => 'Source',
    'tags'         => 'Tags',
];

$data = array_map(static fn(array $r) => [
    'account_name' => $r['account_name'],
    'email'        => $r['email'] ?? '',
    'phone'        => $r['phone'] ?? '',
    'industry'     => $r['industry'] ?? '',
    'source'       => $r['source'] ?? '',
    'tags'         => $r['tags'] ?? '',
], $rows);

Exporter::stream($format, $columns, $data, 'accounts', 'Customer Accounts');
