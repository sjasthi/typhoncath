<section class="card">

    <div class="rfq-board-header">
        <h1>Customer Accounts</h1>

        <button
            type="button"
            class="rfq-create-btn"
            onclick="toggleCustomerForm()">
            +
        </button>
    </div>

    <div id="customerForm" style="display:none; margin-bottom:20px;">

        <h2>Add Customer</h2>

        <form method="POST">

            <input
                type="text"
                name="account_name"
                placeholder="Customer Name"
                required>

            <input
                type="email"
                name="email"
                placeholder="Email">

            <input
                type="text"
                name="phone"
                placeholder="Phone">

            <input
                type="text"
                name="address"
                placeholder="Address">

            <input
                type="text"
                name="industry"
                placeholder="Industry">

            <input
                type="text"
                name="source"
                placeholder="Source">

            <input
                type="text"
                name="tags"
                placeholder="Tags">

            <button
                type="submit"
                name="add_account"
                class="btn btn-primary">
                Add Customer
            </button>

        </form>

    </div>

    <div class="rfq-list-toolbar">

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
                class="btn btn-primary">
                Filter
            </button>

            <a
                href="accounts.php"
                class="btn rfq-list-clear-btn">
                Clear
            </a>

        </form>

    </div>

    <table class="table">

        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Industry</th>
                <th>Source</th>
                <th>Tags</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($accounts)): ?>

            <tr>
                <td colspan="7">
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

                    <td>

                        <form method="POST">

                            <input
                                type="hidden"
                                name="account_id"
                                value="<?= $account['id'] ?>">

                            <button
                                type="submit"
                                name="delete_account"
                                class="btn btn-danger"
                                onclick="return confirm('Delete this customer?');">

                                Delete

                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</section>

<script>
function toggleCustomerForm()
{
    const form = document.getElementById('customerForm');

    if (form.style.display === 'none')
    {
        form.style.display = 'block';
    }
    else
    {
        form.style.display = 'none';
    }
}
</script>