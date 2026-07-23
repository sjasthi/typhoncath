<?php
/**
 * Export endpoint for the Campaigns list (CSV / XML / PDF-via-print).
 */
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Permissions;
use App\Modules\Campaign\CampaignRepository;
use App\Core\DataTable\Exporter;

Auth::requireLogin();

if (!Permissions::can('campaigns.view')) {
    http_response_code(403);
    exit('Forbidden');
}

$format = (string)($_GET['format'] ?? 'csv');
$rows   = CampaignRepository::listTable()->allRows($_GET);

$columns = [
    'id'            => '#',
    'campaign_name' => 'Name',
    'campaign_type' => 'Type',
    'status'        => 'Status',
    'sent_count'    => 'Sent',
    'open_rate'     => 'Open Rate',
    'click_rate'    => 'Click Rate',
    'created_at'    => 'Created',
];

$data = array_map(static fn(array $c) => [
    'id'            => (int)$c['id'],
    'campaign_name' => $c['campaign_name'],
    'campaign_type' => $c['campaign_type'],
    'status'        => $c['status'],
    'sent_count'    => (int)$c['sent_count'],
    'open_rate'     => $c['open_rate'] !== null ? number_format((float)$c['open_rate'], 1) . '%' : '',
    'click_rate'    => $c['click_rate'] !== null ? number_format((float)$c['click_rate'], 1) . '%' : '',
    'created_at'    => date('Y-m-d', strtotime($c['created_at'])),
], $rows);

Exporter::stream($format, $columns, $data, 'campaigns', 'Campaigns');
