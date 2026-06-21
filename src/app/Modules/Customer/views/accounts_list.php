<section class="card">

    <h1>Customer Accounts</h1>

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
            name="add_account">
            Add Customer
        </button>

    </form>

    <hr>

    <form method="GET">

        <input
            type="text"
            name="search"
            placeholder="Customer Name"
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

        <input
            type="text"
            name="industry"
            placeholder="Industry"
            value="<?= htmlspecialchars($_GET['industry'] ?? '') ?>">

        <input
            type="text"
            name="source"
            placeholder="Source"
            value="<?= htmlspecialchars($_GET['source'] ?? '') ?>">

        <button type="submit">
            Filter
        </button>

        <a href="accounts.php">
            Clear
        </a>

    </form>

    <hr>

    <h2>Existing Customers</h2>

    <table border="1" cellpadding="8">

        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>

        <?php foreach ($accounts as $account): ?>

            <tr>

                <td><?= htmlspecialchars($account['account_name']) ?></td>

                <td><?= htmlspecialchars($account['email']) ?></td>

                <td><?= htmlspecialchars($account['phone']) ?></td>

                <td>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="account_id"
                            value="<?= $account['id'] ?>">

                        <button
                            type="submit"
                            name="delete_account"
                            onclick="return confirm('Delete this customer?');">

                            Delete

                        </button>

                    </form>

                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</section>
