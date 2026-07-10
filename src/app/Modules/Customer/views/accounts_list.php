<section class="card">

    <div class="page-header">
        <h1>Customer Accounts</h1>

        <a href="create_account.php" class="add-btn" title="Add a new customer">+</a>
    </div>

    <div class="toolbar">

        <h2 class="rfq-list-title">
            All Customers
        </h2>

        <form method="GET" class="rfq-list-search-form">

            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                placeholder="Search customer..."
                class="form-control">

            <input
                type="text"
                name="industry"
                value="<?= htmlspecialchars($_GET['industry'] ?? '') ?>"
                placeholder="Industry"
                class="form-control">

            <input
                type="text"
                name="source"
                value="<?= htmlspecialchars($_GET['source'] ?? '') ?>"
                placeholder="Source"
                class="form-control">

            <button
                type="submit"
                class="button button-primary">
                Filter
            </button>

            <a
                href="accounts.php"
                class="button btn-ghost">
                Clear
            </a>

            <?php
                $perPageClass = 'form-control rfq-list-perpage-select';
                include __DIR__ . '/../../../Shared/per_page_select.php';
            ?>

        </form>

    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Industry</th>
                <th>Source</th>
                <th>Tags</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($accounts)): ?>

            <tr>
                <td colspan="6">
                    No customers found.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($accounts as $account): ?>

                <tr>

                    <td>
                        <a href="account_detail.php?id=<?= $account['id'] ?>">
                            <?= htmlspecialchars($account['account_name']) ?>
                        </a>
                    </td>

                    <td>
                        <?= htmlspecialchars($account['email']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($account['phone']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($account['industry']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($account['source']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($account['tags']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

    <?php
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
        Showing <?= $pager->from() ?>–<?= $pager->to() ?> of <?= number_format($total) ?> account<?= $total !== 1 ? 's' : '' ?>
    </div>

</section>
