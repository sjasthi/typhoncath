<?php
use App\Core\Permissions;

// Example:
// Permissions::require('manage_users');

// Why it matters

// Logging in is not enough.

// A Marketing User may be logged in, but they should not necessarily manage inventory.

// An Inventory Manager may be logged in, but they should not necessarily create campaigns.

// A Sales User may be logged in, but they should not necessarily manage users.

// So the flow is:

// require_auth.php checks:
// Are you logged in?

// require_role.php checks:
// Are you allowed to do this specific action?
// Example permission checks
// Permissions::require('customers.view');
// Permissions::require('customers.edit');

// Permissions::require('rfqs.view');
// Permissions::require('rfqs.create');
// Permissions::require('rfqs.update_stage');

// Permissions::require('campaigns.view');
// Permissions::require('campaigns.create');

// Permissions::require('inventory.view');
// Permissions::require('inventory.update_stock');

// Permissions::require('admin.manage_users');