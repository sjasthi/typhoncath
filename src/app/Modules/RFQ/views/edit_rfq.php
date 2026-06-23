<section class="card">

    <div class="rfq-board-header">
        <h1>Edit RFQ</h1>
        <a href="/modules/rfq/detail.php?id=<?= (int)$rfq['id'] ?>" class="btn rfq-list-clear-btn">&#8592; Back</a>
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
                class="form-control"
                required
            >
        </div>

        <!-- Account & Contact -->
        <div class="rfq-form-row">

            <div class="rfq-form-group">
                <label for="rfq-account" class="rfq-form-label">Account <span class="rfq-form-required">*</span></label>
                <select id="rfq-account" name="account_id" class="form-control" required>
                    <option value="">— Select account —</option>
                    <?php foreach ($accounts as $account): ?>
                    <option value="<?= $account['id'] ?>"
                        <?= $input['account_id'] == $account['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($account['account_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="rfq-form-group">
                <label for="rfq-contact" class="rfq-form-label">Contact <span class="text-muted">(optional)</span></label>
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
            </div>

        </div>

        <!-- Stage -->
        <div class="rfq-form-group">
            <label for="rfq-stage" class="rfq-form-label">Stage</label>
            <select id="rfq-stage" name="stage" class="form-control rfq-form-stage-select">
                <?php foreach ($stages as $stage): ?>
                <option value="<?= $stage ?>" <?= $input['stage'] === $stage ? 'selected' : '' ?>>
                    <?= $stage ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="rfq-form-group">
            <label for="rfq-description" class="rfq-form-label">Description <span class="text-muted">(optional)</span></label>
            <textarea
                id="rfq-description"
                name="description"
                rows="4"
                class="form-control"
            ><?= htmlspecialchars($input['description'] ?? '') ?></textarea>
        </div>

        <!-- Actions -->
        <div class="rfq-form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/modules/rfq/detail.php?id=<?= (int)$rfq['id'] ?>" class="btn rfq-list-clear-btn">Cancel</a>
        </div>

    </form>

</section>

<script>
// Filter contacts to only show those belonging to the selected account
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
    filterContacts();
}());
</script>
