<?php
namespace App\Modules\Admin;

class AdminController
{
    private AdminRepository $repo;

    public function __construct()
    {
        $this->repo = new AdminRepository();
    }

    // ── Users (placeholder) ───────────────────────────────────────────────────

    public function users(): void
    {
        include __DIR__ . '/views/users.php';
    }

    // ── Permission Matrix ─────────────────────────────────────────────────────

    public function permissionMatrix(): void
    {
        $roles    = $this->repo->allRoles();
        $granted  = $this->repo->rolePermissionsMap();
        $allPerms = $this->buildPermissionList();

        include __DIR__ . '/views/permission_matrix.php';
    }

    public function handleSaveMatrixPost(): void
    {
        $matrix = [];
        foreach ($_POST['permissions'] ?? [] as $roleId => $perms) {
            $matrix[(int)$roleId] = array_values(array_unique((array)$perms));
        }
        $this->repo->savePermissions($matrix);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Permission matrix saved.'];
        header('Location: /admin/users.php');
        exit;
    }

    // ── Canonical permission list ─────────────────────────────────────────────
    // This is the single source of truth for what permissions exist in the system.
    // Adding a new permission here makes it appear in the matrix automatically.

    private function buildPermissionList(): array
    {
        return [
            // Dashboard
            ['module' => 'Dashboard',    'action' => 'dashboard.view',              'label' => 'View Dashboard'],
            // Customers
            ['module' => 'Customers',    'action' => 'customers.view',              'label' => 'View Customers'],
            ['module' => 'Customers',    'action' => 'customers.create',            'label' => 'Create Customers'],
            ['module' => 'Customers',    'action' => 'customers.edit',              'label' => 'Edit Customers'],
            ['module' => 'Customers',    'action' => 'customers.delete',            'label' => 'Delete Customers'],
            // Contacts
            ['module' => 'Contacts',     'action' => 'contacts.view',               'label' => 'View Contacts'],
            ['module' => 'Contacts',     'action' => 'contacts.create',             'label' => 'Create Contacts'],
            ['module' => 'Contacts',     'action' => 'contacts.edit',               'label' => 'Edit Contacts'],
            ['module' => 'Contacts',     'action' => 'contacts.delete',             'label' => 'Delete Contacts'],
            // Interactions
            ['module' => 'Interactions', 'action' => 'interactions.create',         'label' => 'Log Interactions'],
            // RFQ
            ['module' => 'RFQ',          'action' => 'rfqs.view',                   'label' => 'View RFQs'],
            ['module' => 'RFQ',          'action' => 'rfqs.create',                 'label' => 'Create RFQs'],
            ['module' => 'RFQ',          'action' => 'rfqs.edit',                   'label' => 'Edit RFQs'],
            ['module' => 'RFQ',          'action' => 'rfqs.delete',                 'label' => 'Delete RFQs'],
            ['module' => 'RFQ',          'action' => 'rfqs.update_stage',           'label' => 'Change Stage'],
            // Quotes
            ['module' => 'Quotes',       'action' => 'quotes.create',               'label' => 'Create Quotes'],
            ['module' => 'Quotes',       'action' => 'quotes.edit',                 'label' => 'Edit Quotes'],
            ['module' => 'Quotes',       'action' => 'quotes.delete',               'label' => 'Delete Quotes'],
            // Reservations
            ['module' => 'Reservations', 'action' => 'reservations.create',         'label' => 'Create Reservations'],
            ['module' => 'Reservations', 'action' => 'reservations.update_status',  'label' => 'Update Reservation Status'],
            // Campaigns
            ['module' => 'Campaigns',    'action' => 'campaigns.view',              'label' => 'View Campaigns'],
            ['module' => 'Campaigns',    'action' => 'campaigns.create',            'label' => 'Create Campaigns'],
            ['module' => 'Campaigns',    'action' => 'campaigns.edit',              'label' => 'Edit Campaigns'],
            ['module' => 'Campaigns',    'action' => 'campaigns.delete',            'label' => 'Delete Campaigns'],
            ['module' => 'Campaigns',    'action' => 'campaigns.metrics',           'label' => 'Simulate & View Metrics'],
            // Inventory
            ['module' => 'Inventory',    'action' => 'inventory.view',              'label' => 'View Inventory'],
            ['module' => 'Inventory',    'action' => 'inventory.create',            'label' => 'Create Products'],
            ['module' => 'Inventory',    'action' => 'inventory.edit',              'label' => 'Edit Products'],
            ['module' => 'Inventory',    'action' => 'inventory.update_stock',      'label' => 'Update Stock Levels'],
            ['module' => 'Inventory',    'action' => 'inventory.reserve',           'label' => 'Reserve Inventory'],
            // Reports
            ['module' => 'Reports',      'action' => 'reports.view',                'label' => 'View Reports'],
            // Admin
            ['module' => 'Admin',        'action' => 'admin.manage_users',          'label' => 'Manage Users'],
            ['module' => 'Admin',        'action' => 'admin.manage_roles',          'label' => 'Manage Roles & Permissions'],
        ];
    }
}
