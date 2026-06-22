<section class="card">

    <div class="rfq-board-header">
        <h1>Create RFQ</h1>
        <a href="/modules/rfq/pipeline.php" class="btn rfq-list-clear-btn">&#8592; Back</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="rfq-form-errors">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" class="rfq-form">

        <!-- Title -->
        <div class="rfq-form-group">
            <label for="rfq-title" class="rfq-form-label">Title <span class="rfq-form-required">*</span></label>
            <input
                type="text"
                id="rfq-title"
                name="title"
                value="<?= htmlspecialchars($input['title']) ?>"
                placeholder="e.g. Q3 Catheter Supply Request"
                class="form-control"
                required
            >
        </div>

        <!-- Account & Contact (side by side) -->
        <div class="rfq-form-row">

            <div class="rfq-form-group">
                <label for="rfq-account" class="rfq-form-label">Account <span class="rfq-form-required">*</span></label>
                <div class="rfq-form-input-row">
                    <select id="rfq-account" name="account_id" class="form-control" required>
                        <option value="">— Select account —</option>
                        <?php foreach ($accounts as $account): ?>
                        <option value="<?= $account['id'] ?>"
                            <?= $input['account_id'] == $account['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($account['account_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button
                        type="button"
                        class="rfq-create-btn rfq-create-btn--wip"
                        title="⚠ Create Account page not yet available"
                        disabled
                    >+</button>
                </div>
            </div>

            <div class="rfq-form-group">
                <label for="rfq-contact" class="rfq-form-label">Contact <span class="text-muted">(optional)</span></label>
                <div class="rfq-form-input-row">
                    <select id="rfq-contact" name="contact_id" class="form-control">
                        <option value="">— Select contact —</option>
                        <?php foreach ($contacts as $contact): ?>
                        <option
                            value="<?= $contact['id'] ?>"
                            data-account="<?= $contact['account_id'] ?>"
                            <?= $input['contact_id'] == $contact['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                            <?php if ($contact['title']): ?>
                                — <?= htmlspecialchars($contact['title']) ?>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button
                        type="button"
                        class="rfq-create-btn rfq-create-btn--wip"
                        title="⚠ Create Contact page not yet available"
                        disabled
                    >+</button>
                </div>
            </div>

        </div>

        <!-- Stage + quote toggle button -->
        <div class="rfq-form-group">
            <label for="rfq-stage" class="rfq-form-label">Stage</label>
            <div class="rfq-form-input-row">
                <select id="rfq-stage" name="stage" class="form-control rfq-form-stage-select">
                    <?php foreach ($stages as $stage): ?>
                    <option value="<?= $stage ?>" <?= $input['stage'] === $stage ? 'selected' : '' ?>>
                        <?= $stage ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button
                    type="button"
                    id="rfq-add-quote-btn"
                    class="rfq-create-btn rfq-create-btn--wip"
                    title="Select a quote-required stage to add a quote"
                    disabled
                >+</button>
                <span class="rfq-add-label">Quote</span>
            </div>
        </div>

        <!-- Inline quote (shown when a quote-required stage is selected) -->
        <div id="rfq-inline-quote" style="display:none;">
            <div class="rfq-form-row">
                <div class="rfq-form-group">
                    <label for="q-amount" class="rfq-form-label">Quote Amount</label>
                    <input type="number" id="q-amount" name="quote_amount"
                        value="<?= htmlspecialchars($_POST['quote_amount'] ?? '') ?>"
                        placeholder="0.00" step="0.01" min="0" class="form-control">
                </div>
                <div class="rfq-form-group">
                    <label for="q-discount" class="rfq-form-label">Discount <span class="text-muted">(optional)</span></label>
                    <input type="number" id="q-discount" name="quote_discount"
                        value="<?= htmlspecialchars($_POST['quote_discount'] ?? '') ?>"
                        placeholder="0.00" step="0.01" min="0" class="form-control">
                </div>
            </div>
            <div class="rfq-form-row">
                <div class="rfq-form-group">
                    <label for="q-start" class="rfq-form-label">Valid From <span class="text-muted">(optional)</span></label>
                    <input type="date" id="q-start" name="quote_validity_start_date"
                        value="<?= htmlspecialchars($_POST['quote_validity_start_date'] ?? '') ?>"
                        class="form-control">
                </div>
                <div class="rfq-form-group">
                    <label for="q-end" class="rfq-form-label">Valid To <span class="text-muted">(optional)</span></label>
                    <input type="date" id="q-end" name="quote_validity_end_date"
                        value="<?= htmlspecialchars($_POST['quote_validity_end_date'] ?? '') ?>"
                        class="form-control">
                </div>
            </div>
        </div>

        <!-- Inventory toggle button (below quote section) -->
        <div class="rfq-section-btn-row">
            <button
                type="button"
                id="rfq-add-res-btn"
                class="rfq-create-btn rfq-create-btn--wip"
                title="Select a quote-required stage to reserve inventory"
                disabled
            >+</button>
            <span class="rfq-add-label">Reserve Inventory</span>
        </div>

        <!-- Inline inventory reservation — supports multiple items -->
        <div id="rfq-inline-res" style="display:none;">
            <table class="table rfq-res-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="width:100px;">SKU</th>
                        <th style="width:100px;">Unit Price</th>
                        <th style="width:90px;">Qty</th>
                        <th style="width:110px;">Total</th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="rfq-res-tbody"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:500;padding-top:10px;color:#374151;">Catalog Total</td>
                        <td style="padding-top:10px;"><strong id="rfq-res-grand-total">—</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <button type="button" id="rfq-res-add-row" class="btn rfq-list-clear-btn" style="margin-top:4px;">+ Add Item</button>
        </div>

        <!-- Description -->
        <div class="rfq-form-group">
            <label for="rfq-description" class="rfq-form-label">Description <span class="text-muted">(optional)</span></label>
            <textarea
                id="rfq-description"
                name="description"
                rows="4"
                placeholder="Describe the request, quantities, or any relevant details…"
                class="form-control"
            ><?= htmlspecialchars($input['description']) ?></textarea>
        </div>

        <!-- Actions -->
        <div class="rfq-form-actions">
            <button type="submit" class="btn btn-primary">Create RFQ</button>
            <a href="/modules/rfq/pipeline.php" class="btn rfq-list-clear-btn">Cancel</a>
        </div>

    </form>

</section>

<script>
// Enable/disable the + quote button based on stage, expand inline quote section on click
(function () {
    var quoteRequiredStages = <?= json_encode($quoteRequiredStages) ?>;
    var stageSelect  = document.getElementById('rfq-stage');
    var addBtn       = document.getElementById('rfq-add-quote-btn');
    var quoteSection = document.getElementById('rfq-inline-quote');
    var quoteOpen    = <?= (!empty($_POST['quote_amount'])) ? 'true' : 'false' ?>;

    function updateStageBtn() {
        var stage = stageSelect.value;
        var required = quoteRequiredStages.indexOf(stage) !== -1;

        if (required) {
            addBtn.disabled = false;
            addBtn.classList.remove('rfq-create-btn--wip');
            addBtn.title = 'Add a quote for this RFQ';
        } else {
            addBtn.disabled = true;
            addBtn.classList.add('rfq-create-btn--wip');
            addBtn.title = 'Select a quote-required stage to add a quote';
            quoteSection.style.display = 'none';
            quoteOpen = false;
        }
    }

    addBtn.addEventListener('click', function () {
        quoteOpen = !quoteOpen;
        quoteSection.style.display = quoteOpen ? '' : 'none';
    });

    stageSelect.addEventListener('change', updateStageBtn);

    // Restore state on validation re-render
    updateStageBtn();
    if (quoteOpen && quoteRequiredStages.indexOf(stageSelect.value) !== -1) {
        quoteSection.style.display = '';
    }
}());

// Multi-row inventory reservation
(function () {
    var quoteRequiredStages = <?= json_encode($quoteRequiredStages) ?>;
    var PRODUCTS            = <?= json_encode(array_values($products)) ?>;
    var RESTORE             = <?= json_encode([
        'products' => array_values($_POST['res_product_id']        ?? []),
        'quantities'     => array_values($_POST['res_quantity_reserved']  ?? []),
    ]) ?>;

    var stageSelect   = document.getElementById('rfq-stage');
    var resBtn        = document.getElementById('rfq-add-res-btn');
    var resSection    = document.getElementById('rfq-inline-res');
    var tbody         = document.getElementById('rfq-res-tbody');
    var addRowBtn     = document.getElementById('rfq-res-add-row');
    var grandTotalEl  = document.getElementById('rfq-res-grand-total');
    var resOpen       = RESTORE.products.length > 0;

    function syncGrandTotal() {
        var total = 0;
        tbody.querySelectorAll('.rfq-res-row').forEach(function (row) {
            var p  = PRODUCT_MAP[row.querySelector('.res-product-sel').value];
            var qi = row.querySelector('.res-qty-input');
            if (p && qi.value) total += parseFloat(p.price) * parseInt(qi.value, 10);
        });
        grandTotalEl.textContent = total > 0 ? fmt(total) : '—';
    }

    var PRODUCT_MAP = {};
    PRODUCTS.forEach(function (p) { PRODUCT_MAP[String(p.id)] = p; });

    function truncate(str, len) {
        if (!str) return '';
        return str.length > len ? str.slice(0, len) + '…' : str;
    }

    function buildOptions(selectedId) {
        var html = '<option value="">— Select product —</option>';
        PRODUCTS.forEach(function (p) {
            var sel = String(p.id) === String(selectedId) ? ' selected' : '';
            html += '<option value="' + p.id + '" data-available="' + p.available_quantity + '"' + sel + '>'
                  + p.product_name + '</option>';
        });
        return html;
    }

    function fmt(n) { return '$' + parseFloat(n).toFixed(2); }

    function addRow(productId, qty) {
        var tr = document.createElement('tr');
        tr.className = 'rfq-res-row';
        tr.innerHTML =
            '<td>'
            +   '<select name="res_product_id[]" class="form-control res-product-sel">' + buildOptions(productId || '') + '</select>'
            +   '<span class="res-desc text-muted" style="font-size:0.78rem;display:block;margin-top:3px;"></span>'
            +   '<span class="rfq-add-label res-avail-hint" style="display:none;margin-top:2px;"></span>'
            + '</td>'
            + '<td><span class="res-sku text-muted" style="font-size:0.85rem;">—</span></td>'
            + '<td><span class="res-price" style="font-size:0.85rem;">—</span></td>'
            + '<td><input type="number" name="res_quantity_reserved[]" class="form-control res-qty-input"'
            + ' value="' + (qty || '') + '" placeholder="1" min="1" step="1"></td>'
            + '<td><strong class="res-total">—</strong></td>'
            + '<td><button type="button" class="rfq-res-remove-btn" title="Remove row">&times;</button></td>';

        var sel      = tr.querySelector('.res-product-sel');
        var hint     = tr.querySelector('.res-avail-hint');
        var descEl   = tr.querySelector('.res-desc');
        var skuEl    = tr.querySelector('.res-sku');
        var priceEl  = tr.querySelector('.res-price');
        var totalEl  = tr.querySelector('.res-total');
        var qi       = tr.querySelector('.res-qty-input');

        function syncRow() {
            var p = PRODUCT_MAP[sel.value];
            if (!p) {
                hint.style.display = 'none'; descEl.textContent = ''; skuEl.textContent = '—';
                priceEl.textContent = '—'; totalEl.textContent = '—';
                qi.removeAttribute('max');
                syncGrandTotal(); return;
            }
            qi.max = p.available_quantity;
            hint.textContent = p.available_quantity + ' unit' + (p.available_quantity !== 1 ? 's' : '') + ' available';
            hint.style.display = '';
            descEl.textContent  = truncate(p.description, 65);
            skuEl.textContent   = p.sku;
            priceEl.textContent = fmt(p.price);
            syncTotal();
        }

        function syncTotal() {
            var p = PRODUCT_MAP[sel.value];
            if (!p || !qi.value) { totalEl.textContent = '—'; syncGrandTotal(); return; }
            totalEl.textContent = fmt(parseFloat(p.price) * parseInt(qi.value, 10));
            syncGrandTotal();
        }

        sel.addEventListener('change', syncRow);
        qi.addEventListener('input', syncTotal);
        tr.querySelector('.rfq-res-remove-btn').addEventListener('click', function () { tr.remove(); syncGrandTotal(); });
        tbody.appendChild(tr);
        syncRow();
    }

    function updateResBtn() {
        var required = quoteRequiredStages.indexOf(stageSelect.value) !== -1;
        resBtn.disabled = !required;
        resBtn.classList.toggle('rfq-create-btn--wip', !required);
        resBtn.title = required ? 'Reserve inventory for this RFQ' : 'Select a quote-required stage to reserve inventory';
        if (!required) { resSection.style.display = 'none'; resOpen = false; }
    }

    resBtn.addEventListener('click', function () {
        resOpen = !resOpen;
        resSection.style.display = resOpen ? '' : 'none';
        if (resOpen && tbody.rows.length === 0) addRow();
    });

    addRowBtn.addEventListener('click', function () { addRow(); });
    stageSelect.addEventListener('change', updateResBtn);

    updateResBtn();

    // Restore rows after validation failure
    if (resOpen && quoteRequiredStages.indexOf(stageSelect.value) !== -1) {
        resSection.style.display = '';
        if (RESTORE.products.length > 0) {
            RESTORE.products.forEach(function (pid, i) { addRow(pid, RESTORE.quantities[i] || ''); });
        } else {
            addRow();
        }
    }
}());

// Filter the contact dropdown to only show contacts belonging to the selected account
(function () {
    const accountSelect = document.getElementById('rfq-account');
    const contactSelect = document.getElementById('rfq-contact');
    const allOptions    = Array.from(contactSelect.querySelectorAll('option[data-account]'));

    function filterContacts() {
        const accountId = accountSelect.value;

        allOptions.forEach(function (opt) {
            const belongs = opt.dataset.account === accountId;
            opt.hidden   = !belongs;
            if (!belongs && opt.selected) {
                opt.selected = false;
                contactSelect.value = '';
            }
        });
    }

    accountSelect.addEventListener('change', filterContacts);

    // Run on load to restore state after a validation failure
    filterContacts();
}());
</script>
