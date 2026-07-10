<?php
$statusBadge = [
    'In Stock'  => 'rfq-badge-success',
    'Low Stock' => 'rfq-badge-warning',
    'No Stock'  => 'rfq-badge-danger',
];
?>

<section class="card">
    <div class="rfq-board-header">
        <h1>Inventory</h1>
        <a href="/modules/inventory/products.php?page=detail" class="btn btn-primary">+ Add Product</a>
    </div>

    <!-- Search / filter toolbar -->
    <form method="GET" class="rfq-list-toolbar" style="flex-direction:row; flex-wrap:wrap; align-items:center; gap:0.5rem; margin-bottom:1rem;">
        <input
            type="text"
            name="search"
            class="form-control rfq-list-search-input"
            placeholder="Search by name or SKU"
            value="<?= htmlspecialchars($search ?? '') ?>"
            style="max-width:280px; margin-bottom:0;"
        >
        <label style="display:flex; align-items:center; gap:0.4rem; font-weight:600; font-size:0.9rem; margin:0;">
            <input
                type="checkbox"
                name="low_stock"
                value="1"
                <?= ($lowStockOnly ?? false) ? 'checked' : '' ?>
                onchange="this.form.submit()"
            >
            Low Stock Only
        </label>
        <button type="submit" class="btn">Search</button>
        <?php if (!empty($search) || !empty($lowStockOnly)): ?>
            <a href="/modules/inventory/products.php" class="btn rfq-list-clear-btn">Clear</a>
        <?php endif; ?>
        <?php
            $perPageClass = 'form-control rfq-list-perpage-select';
            include __DIR__ . '/../../../Shared/per_page_select.php';
        ?>
    </form>

    <!-- Product table -->
    <table class="table rfq-list-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Available</th>
                <th>Reserved</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" class="rfq-list-empty text-muted">No products found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $p):
                    $available = (int) $p['available_quantity'];
                    if ($available === 0)       $statusLabel = 'No Stock';
                    elseif ($p['low_stock'])    $statusLabel = 'Low Stock';
                    else                        $statusLabel = 'In Stock';
                    $badgeClass = $statusBadge[$statusLabel];
                ?>
                <tr>
                    <td class="rfq-list-id"><?= htmlspecialchars($p['sku']) ?></td>
                    <td>
                        <a href="/modules/inventory/products.php?page=detail&id=<?= (int)$p['id'] ?>" class="rfq-list-link">
                            <?= htmlspecialchars($p['product_name']) ?>
                        </a>
                    </td>
                    <td>$<?= number_format((float)$p['price'], 2) ?></td>
                    <td><?= $available ?></td>
                    <td><?= (int)$p['reserved_quantity'] ?></td>
                    <td><span class="rfq-badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
                    <td style="white-space:nowrap;">
                        <a href="/modules/inventory/products.php?page=detail&id=<?= (int)$p['id'] ?>" class="btn" style="font-size:0.85rem; padding:4px 10px;">Edit</a>
                        <a href="/modules/inventory/products.php?page=stock&id=<?= (int)$p['id'] ?>" class="btn" style="font-size:0.85rem; padding:4px 10px;">Stock</a>
                        <a href="/modules/inventory/products.php?page=delete&id=<?= (int)$p['id'] ?>" class="btn" style="font-size:0.85rem; padding:4px 10px; background:#fde8e8; color:#b91c1c; border-color:#fca5a5;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
        $pageParam = 'p'; // this module reserves ?page= for routing (detail/stock/delete)
        $paginationClasses = [
            'container' => 'rfq-pagination',
            'item'      => 'rfq-page-btn',
            'nav'       => 'rfq-pagination-nav',
            'disabled'  => 'rfq-page-disabled',
            'active'    => 'rfq-page-active',
            'ellipsis'  => 'rfq-page-ellipsis',
        ];
        include __DIR__ . '/../../../Shared/pagination.php';
    ?>
    <div class="rfq-list-footer">
        Showing <?= $pager->from() ?>–<?= $pager->to() ?> of <?= number_format($total) ?> product<?= $total !== 1 ? 's' : '' ?>
    </div>

    <p class="rfq-list-footer"><?= count($products ?? []) ?> product(s)</p>
</section>

<script src="/assets/js/inventory.js"></script>
