<section class="card">
    <h1>Update Stock</h1>

    <?php if (empty($product)): ?>
        <div class="alert alert-danger">Product not found.</div>
        <a href="/modules/inventory/products.php" class="btn mt-3">Back to Inventory</a>
    <?php else: ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p class="text-muted">
            <?= htmlspecialchars($product['product_name']) ?> (SKU: <?= htmlspecialchars($product['sku']) ?>)
        </p>

        <form method="POST" action="/modules/inventory/stock_update.php" class="mt-3">
            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

            <div class="mt-3">
                <label>Available Quantity</label>
                <input
                    type="number"
                    min="0"
                    name="available_quantity"
                    class="form-control"
                    value="<?= (int) $product['available_quantity'] ?>"
                    required
                >
            </div>

            <div class="mt-3">
                <label>Reserved Quantity</label>
                <input
                    type="number"
                    min="0"
                    name="reserved_quantity"
                    class="form-control"
                    value="<?= (int) $product['reserved_quantity'] ?>"
                    required
                >
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save Stock</button>
                <a href="/modules/inventory/product_detail.php?id=<?= (int) $product['id'] ?>" class="btn">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</section>
