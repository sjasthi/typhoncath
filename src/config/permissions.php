<?php
return [
    'Admin' => ['*'],
    'Sales User' => [
        'view_dashboard',
        'manage_customers',
        'manage_rfqs',
        'manage_quotes',
        'request_inventory_reservation',
    ],
    'Marketing User' => [
        'view_dashboard',
        'view_customers',
        'manage_campaigns',
        'view_campaign_metrics',
    ],
    'Inventory Manager' => [
        'view_dashboard',
        'view_customers',
        'view_rfqs',
        'manage_products',
        'manage_inventory',
        'manage_reservations',
    ],
];
