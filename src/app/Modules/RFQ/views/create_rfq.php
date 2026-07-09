<style>
.rfq-search-dropdown { flex: 1; }

.rfq-search-results {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-top: 2px solid var(--primary-blue);
    border-radius: 0 0 6px 6px;
    max-height: 220px;
    overflow-y: auto;
    margin-top: 1px;
}

.rfq-search-option {
    padding: 0.55rem 0.75rem;
    cursor: pointer;
    font-size: 0.9rem;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
}

.rfq-search-option:last-child { border-bottom: none; }

.rfq-search-option:hover,
.rfq-search-option--focused {
    background: #f5f9ff;
    color: var(--primary-blue);
}

.rfq-search-option--empty {
    padding: 0.55rem 0.75rem;
    color: #9ca3af;
    cursor: default;
    font-style: italic;
    font-size: 0.875rem;
}

.rfq-search-input--selected {
    background: #f0f9ff;
    border-color: var(--primary-blue) !important;
}
</style>

<section class="card">

    <div class="module-header">
        <h1>Create RFQ</h1>
        <a href="/modules/rfq/pipeline.php" class="btn btn-secondary">&#8592; Back</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="form-errors">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" class="module-form" id="rfq-create-form">
        <?= App\Core\Csrf::field() ?>

        <!-- Title -->
        <div class="form-group">
            <label for="rfq-title" class="form-label">Title <span class="form-required">*</span></label>
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
        <div class="form-row">

            <div class="form-group">
                <label for="rfq-account-search" class="form-label">Account <span class="text-muted">(optional)</span></label>
                <div class="form-input-row">
                    <div class="rfq-search-dropdown" id="account-dd">
                        <input
                            type="text"
                            id="rfq-account-search"
                            class="form-control rfq-search-input"
                            placeholder="Search accounts…"
                            autocomplete="off"
                        >
                        <input type="hidden" name="account_id" id="rfq-account-val" value="<?= htmlspecialchars($input['account_id']) ?>">
                    </div>
                    <a
                        href="/modules/customer/accounts.php"
                        class="rfq-create-btn"
                        title="Add a new account"
                    >+</a>
                </div>
                 <div class="rfq-search-results" id="rfq-account-results" style="display:none;"></div>

            </div>

            <div class="form-group">
                <label for="rfq-contact-search" class="form-label">Contact <span class="text-muted">(optional)</span></label>
                <div class="form-input-row">
                    <div class="rfq-search-dropdown" id="contact-dd">
                        <input
                            type="text"
                            id="rfq-contact-search"
                            class="form-control rfq-search-input"
                            placeholder="Search contacts…"
                            autocomplete="off"
                        >
                        <input type="hidden" name="contact_id" id="rfq-contact-val" value="<?= htmlspecialchars($input['contact_id']) ?>">
                    </div>
                    <a
                        id="rfq-contact-add-btn"
                        href="/modules/customer/accounts.php"
                        class="rfq-create-btn"
                        title="Add a new contact"
                    >+</a>
                </div>
                     <div class="rfq-search-results" id="rfq-contact-results" style="display:none;"></div>

            </div>

        </div>

        <!-- Stage + quote toggle button -->
        <div class="form-group">
            <label for="rfq-stage" class="form-label">Stage</label>
            <div class="form-input-row">
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
                <span class="field-hint">Quote</span>
            </div>
        </div>

        <!-- Inline quote (shown when a quote-required stage is selected) -->
        <div id="rfq-inline-quote" style="display:none;">
            <div class="form-row">
                <div class="form-group">
                    <label for="q-amount" class="form-label">Quote Amount</label>
                    <input type="number" id="q-amount" name="quote_amount"
                        value="<?= htmlspecialchars($input['quote_amount']) ?>"
                        placeholder="0.00" step="0.01" min="0" class="form-control">
                </div>
                <div class="form-group">
                    <label for="q-discount" class="form-label">Discount <span class="text-muted">(optional)</span></label>
                    <input type="number" id="q-discount" name="quote_discount"
                        value="<?= htmlspecialchars($input['quote_discount']) ?>"
                        placeholder="0.00" step="0.01" min="0" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="q-start" class="form-label">Valid From <span class="text-muted">(optional)</span></label>
                    <input type="date" id="q-start" name="quote_validity_start_date"
                        value="<?= htmlspecialchars($input['quote_validity_start_date']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="q-end" class="form-label">Valid To <span class="text-muted">(optional)</span></label>
                    <input type="date" id="q-end" name="quote_validity_end_date"
                        value="<?= htmlspecialchars($input['quote_validity_end_date']) ?>"
                        class="form-control">
                </div>
            </div>
        </div>

        <!-- Inventory toggle button (below quote section) -->
        <div class="section-toggle-row">
            <button
                type="button"
                id="rfq-add-res-btn"
                class="rfq-create-btn rfq-create-btn--wip"
                title="Select a quote-required stage to reserve inventory"
                disabled
            >+</button>
            <span class="field-hint">Reserve Inventory</span>
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
                        <td colspan="4" class="table-footer-label">Catalog Total</td>
                        <td class="table-footer-value"><strong id="rfq-res-grand-total">—</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <button type="button" id="rfq-res-add-row" class="btn btn-secondary rfq-res-add-row">+ Add Item</button>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="rfq-description" class="form-label">Description <span class="text-muted">(optional)</span></label>
            <textarea
                id="rfq-description"
                name="description"
                rows="4"
                placeholder="Describe the request, quantities, or any relevant details…"
                class="form-control"
            ><?= htmlspecialchars($input['description']) ?></textarea>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create RFQ</button>
            <a href="/modules/rfq/pipeline.php" class="btn btn-secondary">Cancel</a>
        </div>

    </form>

</section>

<script>
// Searchable dropdown factory
(function () {
    const ACCOUNTS = <?= json_encode(array_map(fn($a) => ['id' => $a['id'], 'label' => $a['account_name']], $accounts)) ?>;
    const CONTACTS = <?= json_encode(array_map(fn($c) => [
        'id'         => $c['id'],
        'account_id' => $c['account_id'],
        'label'      => trim($c['first_name'] . ' ' . $c['last_name']) . ($c['title'] ? ' — ' . $c['title'] : ''),
    ], $contacts)) ?>;

    const accountValEl     = document.getElementById('rfq-account-val');
    const contactSearchEl  = document.getElementById('rfq-contact-search');
    const contactValEl     = document.getElementById('rfq-contact-val');

    // When an account is selected, only show its contacts; no account = all contacts
    function getContactItems() {
        const accountId = accountValEl.value;
        if (!accountId) return CONTACTS;
        return CONTACTS.filter(c => String(c.account_id) === String(accountId));
    }

    // getItems is a function so contact list is re-evaluated on every render
    function initSearchDropdown(searchEl, hiddenEl, resultsEl, getItems, onChanged) {
        let focusedIdx = -1;

        if (hiddenEl.value) {
            const match = getItems().find(i => String(i.id) === String(hiddenEl.value));
            if (match) {
                searchEl.value = match.label;
                searchEl.classList.add('rfq-search-input--selected');
            }
        }

        function renderResults(query) {
            const items = getItems();
            const q = query.toLowerCase();
            const filtered = q ? items.filter(i => i.label.toLowerCase().includes(q)) : items;

            resultsEl.innerHTML = '';
            focusedIdx = -1;

            if (filtered.length === 0) {
                resultsEl.innerHTML = '<div class="rfq-search-option--empty">No results</div>';
            } else {
                filtered.forEach((item) => {
                    const div = document.createElement('div');
                    div.className = 'rfq-search-option';
                    div.textContent = item.label;
                    div.dataset.id = item.id;
                    div.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        selectItem(item);
                    });
                    resultsEl.appendChild(div);
                });
            }
        }

        function selectItem(item) {
            hiddenEl.value = item.id;
            searchEl.value = item.label;
            searchEl.classList.add('rfq-search-input--selected');
            resultsEl.style.display = 'none';
            focusedIdx = -1;
            if (onChanged) onChanged(item.id);
        }

        function clearSelection() {
            hiddenEl.value = '';
            searchEl.classList.remove('rfq-search-input--selected');
            if (onChanged) onChanged('');
        }

        searchEl.addEventListener('input', function () {
            clearSelection();
            renderResults(this.value);
            resultsEl.style.display = '';
        });

        searchEl.addEventListener('focus', function () {
            renderResults(this.value);
            resultsEl.style.display = '';
        });

        searchEl.addEventListener('blur', function () {
            setTimeout(() => { resultsEl.style.display = 'none'; }, 150);
            if (!hiddenEl.value && this.value) this.value = '';
        });

        searchEl.addEventListener('keydown', function (e) {
            const opts = resultsEl.querySelectorAll('.rfq-search-option');
            if (!opts.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                focusedIdx = Math.min(focusedIdx + 1, opts.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusedIdx = Math.max(focusedIdx - 1, 0);
            } else if (e.key === 'Enter' && focusedIdx >= 0) {
                e.preventDefault();
                const match = getItems().find(i => String(i.id) === opts[focusedIdx].dataset.id);
                if (match) selectItem(match);
                return;
            } else if (e.key === 'Escape') {
                resultsEl.style.display = 'none';
                return;
            } else {
                return;
            }

            opts.forEach((o, i) => o.classList.toggle('rfq-search-option--focused', i === focusedIdx));
            if (opts[focusedIdx]) opts[focusedIdx].scrollIntoView({ block: 'nearest' });
        });
    }

    initSearchDropdown(
        document.getElementById('rfq-account-search'),
        accountValEl,
        document.getElementById('rfq-account-results'),
        () => ACCOUNTS,
        function (newAccountId) {
            // Point the contact + button to the selected account's detail page, or back to accounts list
            const contactAddBtn = document.getElementById('rfq-contact-add-btn');
            if (contactAddBtn) {
                contactAddBtn.href = newAccountId
                    ? '/modules/customer/account_detail.php?id=' + newAccountId
                    : '/modules/customer/accounts.php';
                contactAddBtn.title = newAccountId
                    ? 'Add a new contact to this account'
                    : 'Add a new contact';
            }
            // If the current contact doesn't belong to the newly selected account, clear it
            if (newAccountId && contactValEl.value) {
                const contact = CONTACTS.find(c => String(c.id) === String(contactValEl.value));
                if (contact && String(contact.account_id) !== String(newAccountId)) {
                    contactValEl.value = '';
                    contactSearchEl.value = '';
                    contactSearchEl.classList.remove('rfq-search-input--selected');
                }
            }
        }
    );

    initSearchDropdown(
        contactSearchEl,
        contactValEl,
        document.getElementById('rfq-contact-results'),
        getContactItems
    );

    // Set initial contact + href if an account is already selected on load
    (function () {
        const contactAddBtn = document.getElementById('rfq-contact-add-btn');
        if (contactAddBtn && accountValEl.value) {
            contactAddBtn.href  = '/modules/customer/account_detail.php?id=' + accountValEl.value;
            contactAddBtn.title = 'Add a new contact to this account';
        }
    }());
}());

// Client-side: at least one of account or contact required
(function () {
    document.getElementById('rfq-create-form').addEventListener('submit', function (e) {
        const accountVal = document.getElementById('rfq-account-val').value;
        const contactVal = document.getElementById('rfq-contact-val').value;
        if (!accountVal && !contactVal) {
            e.preventDefault();
            const existing = this.querySelector('.form-errors');
            if (!existing) {
                const box = document.createElement('div');
                box.className = 'form-errors';
                box.innerHTML = '<p>At least one of Account or Contact is required.</p>';
                this.insertBefore(box, this.firstChild);
            }
            window.scrollTo(0, 0);
        }
    });
}());

// Enable/disable the quote + button based on stage; expand inline quote on click
(function () {
    const quoteRequiredStages = <?= json_encode($quoteRequiredStages) ?>;
    const stageSelect  = document.getElementById('rfq-stage');
    const addBtn       = document.getElementById('rfq-add-quote-btn');
    const quoteSection = document.getElementById('rfq-inline-quote');
    let   quoteOpen    = <?= (!empty($input['quote_amount'])) ? 'true' : 'false' ?>;

    function updateQuoteBtn() {
        const isRequired = quoteRequiredStages.includes(stageSelect.value);
        addBtn.disabled = !isRequired;
        addBtn.classList.toggle('rfq-create-btn--wip', !isRequired);
        addBtn.title = isRequired
            ? 'Add a quote for this RFQ'
            : 'Select a quote-required stage to add a quote';
        if (!isRequired) {
            quoteSection.style.display = 'none';
            quoteOpen = false;
        }
    }

    addBtn.addEventListener('click', function () {
        quoteOpen = !quoteOpen;
        quoteSection.style.display = quoteOpen ? '' : 'none';
    });

    stageSelect.addEventListener('change', updateQuoteBtn);

    updateQuoteBtn();
    if (quoteOpen && quoteRequiredStages.includes(stageSelect.value)) {
        quoteSection.style.display = '';
    }
}());

// Multi-row inventory reservation
(function () {
    const quoteRequiredStages = <?= json_encode($quoteRequiredStages) ?>;
    const PRODUCTS            = <?= json_encode(array_values($products)) ?>;
    const RESTORE             = <?= json_encode([
        'products'   => array_values($_POST['res_product_id']       ?? []),
        'quantities' => array_values($_POST['res_quantity_reserved'] ?? []),
    ]) ?>;

    const stageSelect  = document.getElementById('rfq-stage');
    const resBtn       = document.getElementById('rfq-add-res-btn');
    const resSection   = document.getElementById('rfq-inline-res');
    const tbody        = document.getElementById('rfq-res-tbody');
    const addRowBtn    = document.getElementById('rfq-res-add-row');
    const grandTotalEl = document.getElementById('rfq-res-grand-total');
    let   resOpen      = RESTORE.products.length > 0;

    const PRODUCT_MAP = {};
    PRODUCTS.forEach(p => { PRODUCT_MAP[String(p.id)] = p; });

    function fmt(n) { return '$' + parseFloat(n).toFixed(2); }

    function truncate(str, len) {
        return str && str.length > len ? str.slice(0, len) + '…' : (str || '');
    }

    function buildOptions(selectedId) {
        return '<option value="">— Select product —</option>'
            + PRODUCTS.map(p => {
                const sel = String(p.id) === String(selectedId) ? ' selected' : '';
                return `<option value="${p.id}" data-available="${p.available_quantity}"${sel}>${p.product_name}</option>`;
            }).join('');
    }

    function syncGrandTotal() {
        let total = 0;
        tbody.querySelectorAll('.rfq-res-row').forEach(row => {
            const p  = PRODUCT_MAP[row.querySelector('.res-product-sel').value];
            const qi = row.querySelector('.res-qty-input');
            if (p && qi.value) total += parseFloat(p.price) * parseInt(qi.value, 10);
        });
        grandTotalEl.textContent = total > 0 ? fmt(total) : '—';
    }

    function addRow(productId, qty) {
        const tr = document.createElement('tr');
        tr.className = 'rfq-res-row';
        tr.innerHTML = `
            <td>
                <select name="res_product_id[]" class="form-control res-product-sel">
                    ${buildOptions(productId || '')}
                </select>
                <span class="res-desc text-muted"></span>
                <span class="field-hint res-avail-hint"></span>
            </td>
            <td><span class="res-sku text-muted">—</span></td>
            <td><span class="res-price">—</span></td>
            <td>
                <input type="number" name="res_quantity_reserved[]" class="form-control res-qty-input"
                    value="${qty || ''}" placeholder="1" min="1" step="1">
            </td>
            <td><strong class="res-total">—</strong></td>
            <td><button type="button" class="rfq-res-remove-btn" title="Remove row">&times;</button></td>
        `;

        const sel      = tr.querySelector('.res-product-sel');
        const hint     = tr.querySelector('.res-avail-hint');
        const descEl   = tr.querySelector('.res-desc');
        const skuEl    = tr.querySelector('.res-sku');
        const priceEl  = tr.querySelector('.res-price');
        const totalEl  = tr.querySelector('.res-total');
        const qi       = tr.querySelector('.res-qty-input');

        function syncRow() {
            const p = PRODUCT_MAP[sel.value];
            if (!p) {
                hint.style.display = 'none';
                descEl.textContent = '';
                skuEl.textContent  = '—';
                priceEl.textContent = '—';
                totalEl.textContent = '—';
                qi.removeAttribute('max');
                syncGrandTotal();
                return;
            }
            qi.max              = p.available_quantity;
            hint.textContent    = `${p.available_quantity} unit${p.available_quantity !== 1 ? 's' : ''} available`;
            hint.style.display  = '';
            descEl.textContent  = truncate(p.description, 65);
            skuEl.textContent   = p.sku;
            priceEl.textContent = fmt(p.price);
            syncTotal();
        }

        function syncTotal() {
            const p = PRODUCT_MAP[sel.value];
            if (!p || !qi.value) { totalEl.textContent = '—'; syncGrandTotal(); return; }
            totalEl.textContent = fmt(parseFloat(p.price) * parseInt(qi.value, 10));
            syncGrandTotal();
        }

        sel.addEventListener('change', syncRow);
        qi.addEventListener('input', syncTotal);
        tr.querySelector('.rfq-res-remove-btn').addEventListener('click', () => { tr.remove(); syncGrandTotal(); });
        tbody.appendChild(tr);
        syncRow();
    }

    function updateResBtn() {
        const isRequired = quoteRequiredStages.includes(stageSelect.value);
        resBtn.disabled = !isRequired;
        resBtn.classList.toggle('rfq-create-btn--wip', !isRequired);
        resBtn.title = isRequired
            ? 'Reserve inventory for this RFQ'
            : 'Select a quote-required stage to reserve inventory';
        if (!isRequired) { resSection.style.display = 'none'; resOpen = false; }
    }

    resBtn.addEventListener('click', function () {
        resOpen = !resOpen;
        resSection.style.display = resOpen ? '' : 'none';
        if (resOpen && tbody.rows.length === 0) addRow();
    });

    addRowBtn.addEventListener('click', () => addRow());
    stageSelect.addEventListener('change', updateResBtn);

    updateResBtn();

    // Restore rows after a validation failure re-render
    if (resOpen && quoteRequiredStages.includes(stageSelect.value)) {
        resSection.style.display = '';
        if (RESTORE.products.length > 0) {
            RESTORE.products.forEach((pid, i) => addRow(pid, RESTORE.quantities[i] || ''));
        } else {
            addRow();
        }
    }
}());
</script>
