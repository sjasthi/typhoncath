<section class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h1>Products List</h1>
        <a href="/modules/inventory/product_detail.php" class="btn btn-primary">+ Add Product</a>
    </div>

    <form method="GET" style="display:flex; gap:0.75rem; align-items:center; margin-bottom:1rem;">
        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search by name or SKU"
            value="<?= htmlspecialchars($search ?? '') ?>"
            style="max-width:300px;"
        >
        <label style="display:flex; align-items:center; gap:0.4rem;">
            <input type="checkbox" name="low_stock" value="1"
                <?= ($lowStockOnly ?? false) ? 'checked' : '' ?>
                onchange="this.form.submit()">
            Low Stock Only
        </label>
        <button type="submit" class="btn">Search</button>
    </form>

    <table class="table">
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
                    <td colspan="7" class="text-muted">No products found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr style="<?= $product['low_stock'] ? 'background:#fffbeb;' : '' ?>">
                        <td><?= htmlspecialchars($product['sku']) ?></td>
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td>$<?= number_format((float) $product['price'], 2) ?></td>
                        <td><?= (int) $product['available_quantity'] ?></td>
                        <td><?= (int) $product['reserved_quantity'] ?></td>
                        <td>
                            <?php if ($product['low_stock']): ?>
                                <span style="color:var(--tc-warning); font-weight:600;">Low Stock</span>
                            <?php else: ?>
                                <span style="color:var(--tc-success); font-weight:600;">In Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/modules/inventory/product_detail.php?id=<?= (int) $product['id'] ?>" class="btn">Edit</a>
                            <a href="/modules/inventory/stock_update.php?id=<?= (int) $product['id'] ?>" class="btn">Update Stock</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<script src="/assets/js/inventory.js"></script>
