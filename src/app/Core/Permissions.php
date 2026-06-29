<?php
namespace App\Core;


// Example permissions could be:

// customers.view
// customers.edit
// rfqs.create
// rfqs.update_stage
// campaigns.create
// inventory.update_stock
// admin.manage_users



// EXAMPLE
// On an RFQ create page:

// use App\Core\Auth;
// use App\Core\Permissions;

// Auth::requireLogin();
// Permissions::require('rfqs.create');

// That means:

// User must be logged in
// AND
// User must have permission to create RFQs
class Permissions
{
    public static function can(string $permission): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $role = $user['role'] ?? '';
        $map  = require __DIR__ . '/../../config/permissions.php';
        $rolePermissions = $map[$role] ?? [];

        if (in_array('*', $rolePermissions, true)) {
            return true;
        }

        return in_array($permission, $rolePermissions, true);
    }

    public static function require(string $permission): void
    {
        if (!self::can($permission)) {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }
    }
}
