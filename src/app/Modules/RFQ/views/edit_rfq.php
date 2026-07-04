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
        <h1>Edit RFQ</h1>
        <a href="/modules/rfq/detail.php?id=<?= (int)$rfq['id'] ?>" class="btn btn-secondary">&#8592; Back</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="form-errors">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" class="module-form" id="rfq-edit-form">

        <!-- Title -->
        <div class="form-group">
            <label for="rfq-title" class="form-label">Title <span class="form-required">*</span></label>
            <input
                type="text"
                id="rfq-title"
                name="title"
                value="<?= htmlspecialchars($input['title']) ?>"
                class="form-control"
                required
            >
        </div>

        <!-- Account & Contact -->
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
                        href="/modules/customer/create_account.php"
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
                        href="/modules/customer/create_account.php"
                        class="rfq-create-btn"
                        title="Add a new contact"
                    >+</a>
                </div>
                <div class="rfq-search-results" id="rfq-contact-results" style="display:none;"></div>
            </div>

        </div>

        <!-- Stage -->
        <div class="form-group">
            <label for="rfq-stage" class="form-label">Stage</label>
            <select id="rfq-stage" name="stage" class="form-control rfq-form-stage-select">
                <?php foreach ($stages as $stage): ?>
                <option value="<?= $stage ?>" <?= $input['stage'] === $stage ? 'selected' : '' ?>>
                    <?= $stage ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="rfq-description" class="form-label">Description <span class="text-muted">(optional)</span></label>
            <textarea
                id="rfq-description"
                name="description"
                rows="4"
                class="form-control"
            ><?= htmlspecialchars($input['description'] ?? '') ?></textarea>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/modules/rfq/detail.php?id=<?= (int)$rfq['id'] ?>" class="btn btn-secondary">Cancel</a>
        </div>

    </form>

</section>

<!-- ── Inventory Reservations ────────────────────────── -->
<section class="card">
    <div class="module-header">
        <h2 class="rfq-detail-card-title">Inventory Reservations</h2>
        <a href="/modules/rfq/create_reservation.php?rfq_id=<?= (int)$rfq['id'] ?>"
           class="rfq-create-btn" title="Reserve inventory">+</a>
    </div>

    <?php if (empty($reservations)): ?>
        <p class="text-muted">No inventory reserved for this RFQ.</p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Unit Price</th>
                <th>Qty Reserved</th>
                <th>Total</th>
                <th>Status</th>
                <th style="width:90px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $res): ?>
            <?php
                $resBadge = match($res['reservation_status']) {
                    'Reserved'  => 'rfq-badge-info',
                    'Released'  => 'rfq-badge-neutral',
                    'Converted' => 'rfq-badge-success',
                    default     => '',
                };
            ?>
            <tr>
                <td><?= htmlspecialchars($res['product_name']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($res['sku']) ?></td>
                <td>$<?= number_format((float)$res['price'], 2) ?></td>
                <td><?= (int)$res['quantity_reserved'] ?></td>
                <td><strong>$<?= number_format((float)$res['price'] * (int)$res['quantity_reserved'], 2) ?></strong></td>
                <td><span class="rfq-badge <?= $resBadge ?>"><?= htmlspecialchars($res['reservation_status']) ?></span></td>
                <td style="display:flex;gap:4px;align-items:center;">
                    <?php if ($res['reservation_status'] === 'Reserved'): ?>
                    <a href="/modules/rfq/edit_reservation.php?id=<?= (int)$res['id'] ?>"
                       class="btn btn-secondary" style="font-size:0.78rem;padding:3px 8px;">Edit</a>
                    <?php endif; ?>
                    <form method="POST" action="/modules/rfq/edit.php?id=<?= (int)$rfq['id'] ?>" style="display:inline;"
                          onsubmit="return confirm('Remove this reservation?<?= $res['reservation_status'] === 'Reserved' ? ' Stock will be returned to inventory.' : '' ?>');">
                        <input type="hidden" name="_action"        value="delete_reservation">
                        <input type="hidden" name="reservation_id" value="<?= (int)$res['id'] ?>">
                        <input type="hidden" name="rfq_id"         value="<?= (int)$rfq['id'] ?>">
                        <button type="submit" class="rfq-res-remove-btn" title="Remove reservation">&times;</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<script>
(function () {
    const ACCOUNTS = <?= json_encode(array_map(fn($a) => ['id' => $a['id'], 'label' => $a['account_name']], $accounts)) ?>;
    const CONTACTS = <?= json_encode(array_map(fn($c) => [
        'id'         => $c['id'],
        'account_id' => $c['account_id'],
        'label'      => trim($c['first_name'] . ' ' . $c['last_name']) . ($c['title'] ? ' — ' . $c['title'] : ''),
    ], $contacts)) ?>;

    const accountValEl    = document.getElementById('rfq-account-val');
    const contactSearchEl = document.getElementById('rfq-contact-search');
    const contactValEl    = document.getElementById('rfq-contact-val');

    function getContactItems() {
        const accountId = accountValEl.value;
        if (!accountId) return CONTACTS;
        return CONTACTS.filter(c => String(c.account_id) === String(accountId));
    }

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

    document.getElementById('rfq-edit-form').addEventListener('submit', function (e) {
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
</script>
