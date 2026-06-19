<!-- sidebar.php = the left navigation menu

This file usually contains the links to the main CRM sections. -->

<!-- 
What it does

It lets users move between modules.

Each module should not create its own navigation menu.

Instead, every page should include the same sidebar.

Example:

<?php include __DIR__ . '/../../../Shared/sidebar.php'; ?>
Role-based sidebar

Eventually, the sidebar should only show links the user is allowed to access. -->



<div class="app-shell">
    <aside class="app-sidebar">
        <div class="app-brand">Typhon CRM</div>
        <nav class="app-nav">
            <a href="/dashboard.php">Dashboard</a>
            <a href="/modules/customer/accounts.php">Customers</a>
            <a href="/modules/rfq/pipeline.php">RFQ Pipeline</a>
            <a href="/modules/campaign/campaigns.php">Campaigns</a>
            <a href="/modules/inventory/products.php">Inventory</a>
            <a href="/admin/users.php">Admin</a>
            <a href="/logout.php">Logout</a>
        </nav>
    </aside>
    <main class="app-main">
