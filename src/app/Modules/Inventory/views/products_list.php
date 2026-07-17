<?php
$statusBadge = [
    'In Stock'  => 'rfq-badge-success',
    'Low Stock' => 'rfq-badge-low-stock',
    'No Stock'  => 'rfq-badge-out-of-stock',
];

// Sort-link helper for the product table's column headers. Toggles ASC/DESC
// when clicking the currently-sorted column, otherwise defaults to ASC.
// Mirrors RFQ\views\pipeline_board.php's rfqSortUrl()/rfqSortIcon(), kept as a
// separate function (not shared) since the two views are never included in
// the same request.
function inventorySortUrl(string $col, string $cur, string $dir, string $search, array $statuses, string $perPage): string
{
    $nextDir = ($cur === $col && $dir === 'ASC') ? 'DESC' : 'ASC';
    $params  = array_filter(['search' => $search, 'sort' => $col, 'dir' => $nextDir], fn($v) => $v !== '');
    if (!empty($statuses)) $params['status'] = $statuses;
    if ($perPage !== '25') $params['per_page'] = $perPage;
    return '?' . http_build_query($params);
}

function inventorySortIcon(string $col, string $cur, string $dir): string
{
    if ($cur !== $col) return '<span class="rfq-sort-icon rfq-sort-idle">↕</span>';
    return $dir === 'ASC'
        ? '<span class="rfq-sort-icon rfq-sort-asc">▲</span>'
        : '<span class="rfq-sort-icon rfq-sort-desc">▼</span>';
}

$sortCols = [
    'sku'                => 'SKU',
    'product_name'       => 'Product Name',
    'price'              => 'Price',
    'available_quantity' => 'Available',
    'reserved_quantity'  => 'Reserved',
];
?>

<section class="card">
    <div class="rfq-board-header">
        <h1>Inventory</h1>
        <div style="display:flex; gap:0.5rem;">
            <a href="/modules/inventory/products.php?page=ledger" class="btn">Inventory Ledger</a>
            <a href="/modules/inventory/products.php?page=detail" class="btn btn-primary">+ Add Product</a>
        </div>
    </div>

    <!-- Search / filter toolbar -->
    <form method="GET" class="rfq-list-toolbar" style="flex-direction:row; flex-wrap:wrap; align-items:center; gap:0.5rem; margin-bottom:1rem;">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
        <input
            type="text"
            name="search"
            class="form-control rfq-list-search-input"
            placeholder="Search by name or SKU"
            value="<?= htmlspecialchars($search ?? '') ?>"
            style="max-width:280px; margin-bottom:0;"
        >

        <!-- Column filter on Status (In Stock / Low Stock / No Stock) -->
        <div class="rfq-stage-checks">
            <?php foreach (\App\Modules\Inventory\InventoryRepository::$statuses as $s): ?>
            <label class="rfq-stage-check">
                <input type="checkbox" name="status[]" value="<?= htmlspecialchars($s) ?>"
                    <?= in_array($s, $statuses, true) ? 'checked' : '' ?>>
                <?= htmlspecialchars($s) ?>
            </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn">Filter</button>
        <?php if (!empty($search) || !empty($statuses)): ?>
            <a href="/modules/inventory/products.php?sort=<?= urlencode($sort) ?>&dir=<?= urlencode($dir) ?>" class="btn rfq-list-clear-btn">Clear</a>
        <?php endif; ?>
        <?php
            $perPageClass = 'form-control rfq-list-perpage-select';
            $perPageAutoSubmit = true;
            include __DIR__ . '/../../../Shared/per_page_select.php';
        ?>
    </form>

    <!-- Product table -->
    <table class="table rfq-list-table">
        <thead>
            <tr>
                <?php foreach ($sortCols as $key => $label): ?>
                <th>
                    <a href="<?= inventorySortUrl($key, $sort, $dir, $search ?? '', $statuses, (string)$pager->perPageValue) ?>" class="rfq-sort-link">
                        <?= $label ?>
                        <?= inventorySortIcon($key, $sort, $dir) ?>
                    </a>
                </th>
                <?php endforeach; ?>
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
                        <a href="/modules/inventory/products.php?page=ledger&product_id=<?= (int)$p['id'] ?>" class="btn" style="font-size:0.85rem; padding:4px 10px;">History</a>
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
</section>

<script src="/assets/js/inventory.js"></script>
