<?php
// TODO: CONFIRM THESE ARE THE ONLY PERMISSIONS
return [
    'Super Admin' => [
        '*',
    ],

    'Admin' => [
        'dashboard.view',
        'customers.view',
        'customers.create',
        'customers.edit',
        'customers.delete',
        'rfqs.view',
        'rfqs.create',
        'rfqs.edit',
        'rfqs.update_stage',
        'quotes.create',
        'quotes.edit',
        'campaigns.view',
        'campaigns.create',
        'campaigns.edit',
        'inventory.view',
        'inventory.create',
        'inventory.edit',
        'inventory.update_stock',
        'reports.view',
    ],

    'Sales User' => [
        'dashboard.view',
        'customers.view',
        'customers.create',
        'customers.edit',
        'interactions.create',
        'rfqs.view',
        'rfqs.create',
        'rfqs.edit',
        'rfqs.update_stage',
        'quotes.create',
        'quotes.edit',
        'inventory.view',
        'reports.view',
    ],

    'Marketing User' => [
        'dashboard.view',
        'customers.view',
        'campaigns.view',
        'campaigns.create',
        'campaigns.edit',
        'campaigns.metrics',
        'reports.view',
    ],

    'Inventory Manager' => [
        'dashboard.view',
        'inventory.view',
        'inventory.create',
        'inventory.edit',
        'inventory.update_stock',
        'inventory.reserve',
        'rfqs.view',
        'reports.view',
    ],
];