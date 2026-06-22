<section class="card">

    <div class="rfq-board-header">
        <h1>Add Inventory Reservation</h1>
        <a href="/modules/rfq/detail.php?id=<?= (int)$rfq['id'] ?>" class="btn rfq-list-clear-btn">&#8592; Back</a>
    </div>

    <p class="text-muted">
        RFQ: <strong><?= htmlspecialchars($rfq['title']) ?></strong>
        &mdash; <?= htmlspecialchars($rfq['account_name']) ?>
    </p>

    <?php if (!empty($errors)): ?>
    <div class="rfq-form-errors">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" class="rfq-form">
        <input type="hidden" name="rfq_id" value="<?= (int)$rfq['id'] ?>">

        <!-- Product -->
        <div class="rfq-form-group">
            <label for="res-product" class="rfq-form-label">Product <span class="rfq-form-required">*</span></label>
            <select id="res-product" name="product_id" class="form-control" required>
                <option value="">— Select product —</option>
                <?php foreach ($products as $product): ?>
                <option
                    value="<?= (int)$product['id'] ?>"
                    data-available="<?= (int)$product['available_quantity'] ?>"
                    data-price="<?= (float)$product['price'] ?>"
                    data-sku="<?= htmlspecialchars($product['sku']) ?>"
                    data-desc="<?= htmlspecialchars(mb_strimwidth($product['description'] ?? '', 0, 70, '…')) ?>"
                    <?= $input['product_id'] == $product['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($product['product_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Product detail card (populated by JS) -->
        <div id="res-product-info" style="display:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px 16px;margin-top:-4px;margin-bottom:16px;">
            <div style="display:flex;gap:24px;align-items:baseline;flex-wrap:wrap;">
                <span><span class="text-muted" style="font-size:0.8rem;">SKU</span>&nbsp;<strong id="res-info-sku"></strong></span>
                <span><span class="text-muted" style="font-size:0.8rem;">Unit Price</span>&nbsp;<strong id="res-info-price"></strong></span>
                <span><span class="text-muted" style="font-size:0.8rem;">Available</span>&nbsp;<strong id="res-info-avail"></strong></span>
            </div>
            <p id="res-info-desc" class="text-muted" style="margin:6px 0 0;font-size:0.85rem;"></p>
        </div>

        <!-- Quantity -->
        <div class="rfq-form-group">
            <label for="res-qty" class="rfq-form-label">Quantity <span class="rfq-form-required">*</span></label>
            <input
                type="number"
                id="res-qty"
                name="quantity_reserved"
                value="<?= htmlspecialchars($input['quantity_reserved']) ?>"
                placeholder="1"
                min="1"
                step="1"
                class="form-control"
                style="max-width:160px;"
                required
            >
            <p id="res-total-line" class="text-muted" style="margin-top:6px;font-size:0.9rem;display:none;"></p>
        </div>

        <div class="rfq-form-actions">
            <button type="submit" class="btn btn-primary">Reserve</button>
            <a href="/modules/rfq/detail.php?id=<?= (int)$rfq['id'] ?>" class="btn rfq-list-clear-btn">Cancel</a>
        </div>

    </form>

</section>

<script>
(function () {
    var productSelect = document.getElementById('res-product');
    var qtyInput      = document.getElementById('res-qty');
    var infoCard      = document.getElementById('res-product-info');
    var infoSku       = document.getElementById('res-info-sku');
    var infoPrice     = document.getElementById('res-info-price');
    var infoAvail     = document.getElementById('res-info-avail');
    var infoDesc      = document.getElementById('res-info-desc');
    var totalLine     = document.getElementById('res-total-line');

    function fmt(n) { return '$' + parseFloat(n).toFixed(2); }

    function updateProduct() {
        var opt = productSelect.options[productSelect.selectedIndex];
        if (!opt || !opt.value) {
            infoCard.style.display = 'none';
            qtyInput.removeAttribute('max');
            totalLine.style.display = 'none';
            return;
        }
        var avail = parseInt(opt.dataset.available, 10);
        qtyInput.max    = avail;
        infoSku.textContent   = opt.dataset.sku;
        infoPrice.textContent = fmt(opt.dataset.price);
        infoAvail.textContent = avail + ' unit' + (avail !== 1 ? 's' : '');
        infoDesc.textContent  = opt.dataset.desc;
        infoCard.style.display = '';
        updateTotal();
    }

    function updateTotal() {
        var opt = productSelect.options[productSelect.selectedIndex];
        if (!opt || !opt.value || !qtyInput.value) { totalLine.style.display = 'none'; return; }
        var total = parseFloat(opt.dataset.price) * parseInt(qtyInput.value, 10);
        totalLine.textContent  = opt.dataset.sku + '  ×  ' + qtyInput.value + '  ×  ' + fmt(opt.dataset.price) + '  =  ' + fmt(total);
        totalLine.style.display = '';
    }

    productSelect.addEventListener('change', updateProduct);
    qtyInput.addEventListener('input', updateTotal);
    updateProduct();
}());
</script>
