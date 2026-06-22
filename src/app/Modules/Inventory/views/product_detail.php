<section class="card">
    <h1><?= isset($product) ? 'Edit Product' : 'Add Product' ?></h1>

    <?php if (!empty($_GET['saved'])): ?>
        <div class="alert alert-success">Product saved successfully.</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/modules/inventory/product_detail.php" class="mt-3">
        <?php if (isset($product) && $product !== null): ?>
            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
        <?php endif; ?>

        <div class="mt-3">
            <label>Product Name</label>
            <input
                type="text"
                name="product_name"
                class="form-control"
                value="<?= htmlspecialchars($product['product_name'] ?? '') ?>"
                required
            >
        </div>

        <div class="mt-3">
            <label>SKU</label>
            <input
                type="text"
                name="sku"
                class="form-control"
                value="<?= htmlspecialchars($product['sku'] ?? '') ?>"
                required
            >
        </div>

        <div class="mt-3">
            <label>Price</label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="price"
                class="form-control"
                value="<?= htmlspecialchars((string) ($product['price'] ?? '0.00')) ?>"
                required
            >
        </div>

        <div class="mt-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <?php if (!isset($product) || $product === null): ?>
            <div class="mt-3">
                <label>Starting Available Quantity</label>
                <input
                    type="number"
                    min="0"
                    name="available_quantity"
                    class="form-control"
                    value="0"
                >
            </div>
        <?php endif; ?>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save Product</button>
            <a href="/modules/inventory/products.php" class="btn">Cancel</a>
        </div>
    </form>

    <?php if (isset($product) && $product !== null): ?>
        <div class="mt-3" style="border-top:1px solid #e0e0e0; padding-top:1rem;">
            <p class="text-muted">
                Current stock: <?= (int) $product['available_quantity'] ?> available,
                <?= (int) $product['reserved_quantity'] ?> reserved.
            </p>
            <a href="/modules/inventory/stock_update.php?id=<?= (int) $product['id'] ?>" class="btn">Update Stock</a>
        </div>
    <?php endif; ?>
</section>
