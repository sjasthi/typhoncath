<div class="app-shell">
    <aside class="app-sidebar" id="app-sidebar">
        <div class="app-sidebar-top">
            <div class="app-brand">Typhon CRM</div>
            <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">&#8249;</button>
        </div>
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
        <button class="sidebar-open-btn" id="sidebar-open-btn" aria-label="Open sidebar">&#9776;</button>

        <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="flash-banner flash-banner--<?= htmlspecialchars($flash['type']) ?>" role="alert" id="flash-banner">
            <span><?= htmlspecialchars($flash['message']) ?></span>
            <button class="flash-banner-close" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
        </div>
        <?php endif; ?>
