<?php

require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

Auth::requireLogin();

$accountId = (int)($_GET['id'] ?? 0);

$db = Database::connection();

/*
|--------------------------------------------------------------------------
| Add Interaction
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['add_interaction'])
) {

$stmt = $db->prepare("
    INSERT INTO interactions
    (
        account_id,
        user_id,
        interaction_type,
        interaction_date,
        interaction_subject,
        notes

    )
    VALUES
    (
        :account_id,
        :user_id,
        :interaction_type,
        NOW(),
        :interaction_subject,
        :notes
    )
");

// $stmt->execute([
//     'account_id'       => $accountId,
//     'user_id'          => $_SESSION['user']['id'],
//     'interaction_type' => strtolower($_POST['interaction_type']),
//     'interaction_date' => strtolower($_POST['interaction_date']),
//     'interaction_subject' => strtolower($_POST['subject']),
//     'notes' => strtolower($_POST['notes']),

// ]);

$stmt->execute([
    'account_id' => $accountId,
    'user_id' => $_SESSION['user']['id'],
    'interaction_type' => $_POST['interaction_type'] ?? '',
    'interaction_subject' => $_POST['subject'] ?? '',
    'notes' => $_POST['notes'] ?? ''
]);

    header(
        "Location: account_detail.php?id={$accountId}"
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Account
|--------------------------------------------------------------------------
*/

$stmt = $db->prepare("
    SELECT *
    FROM accounts
    WHERE id = :id
");

$stmt->execute([
    'id' => $accountId
]);

$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {

    die('Account not found.');
}

/*
|--------------------------------------------------------------------------
| Contacts
|--------------------------------------------------------------------------
*/

$stmt = $db->prepare("
    SELECT *
    FROM contacts
    WHERE account_id = :id
    ORDER BY last_name, first_name
");

$stmt->execute([
    'id' => $accountId
]);

$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Interactions
|--------------------------------------------------------------------------
*/

$stmt = $db->prepare("
    SELECT *
    FROM interactions
    WHERE account_id = :id
    ORDER BY interaction_date DESC
");

$stmt->execute([
    'id' => $accountId
]);

$interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';

?>

<section class="card">

    <h1>
        <?= htmlspecialchars($account['account_name']) ?>
    </h1>

    <a href="accounts.php">
        ← Back to Customers
    </a>

    <hr>

    <h2>Account Information</h2>

    <table class="table">

        <tr>
            <th>Email</th>
            <td>
                <?= htmlspecialchars($account['email']) ?>
            </td>
        </tr>

        <tr>
            <th>Phone</th>
            <td>
                <?= htmlspecialchars($account['phone']) ?>
            </td>
        </tr>

        <tr>
            <th>Address</th>
            <td>
                <?= htmlspecialchars($account['address']) ?>
            </td>
        </tr>

        <tr>
            <th>Industry</th>
            <td>
                <?= htmlspecialchars($account['industry']) ?>
            </td>
        </tr>

        <tr>
            <th>Source</th>
            <td>
                <?= htmlspecialchars($account['source']) ?>
            </td>
        </tr>

        <tr>
            <th>Tags</th>
            <td>
                <?= htmlspecialchars($account['tags']) ?>
            </td>
        </tr>

    </table>

</section>

<section class="card">

    <h2>Contacts</h2>

    <?php if (empty($contacts)): ?>

        <p>No contacts found.</p>

    <?php else: ?>

        <table class="table">

            <thead>

                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($contacts as $contact): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $contact['first_name']
                                . ' '
                                . $contact['last_name']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $contact['email']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $contact['phone']
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</section>

<section class="card">

    <h2>Log Interaction</h2>

    <div class="interaction-form">

        <form method="POST">

            <div style="margin-bottom:15px;">

                <label>
                    Interaction Type
                </label>

                <br>

                <select
                    name="interaction_type"
                    required>

                    <option value="">
                        Select Type
                    </option>

                    <option value="Call">
                        Call
                    </option>

                    <option value="Email">
                        Email
                    </option>

                    <option value="Meeting">
                        Meeting
                    </option>

                    <option value="Note">
                        Note
                    </option>

                </select>

            </div>

            <div style="margin-bottom:15px;">

                <label>
                    Subject
                </label>

                <br>

                <input
                    type="text"
                    name="subject"
                    placeholder="Subject"
                    required
                    style="width:100%; max-width:500px;">

            </div>

            <div style="margin-bottom:15px;">

                <label>
                    Notes
                </label>

                <br>

                <textarea
                    name="notes"
                    rows="5"
                    placeholder="Interaction Notes"
                    style="width:100%; max-width:700px;"></textarea>

            </div>

            <button
                type="submit"
                name="add_interaction"
                class="btn btn-primary">

                Save Interaction

            </button>

        </form>

    </div>

</section>

<section class="card">

    <h2>Interaction History</h2>

    <?php if (empty($interactions)): ?>

        <p>No interactions recorded.</p>

    <?php else: ?>

        <?php foreach ($interactions as $interaction): ?>

            <div
                style="
                    border:1px solid #ddd;
                    padding:12px;
                    margin-bottom:12px;
                    border-radius:6px;
                ">

                <strong>
                <?= htmlspecialchars(
                    ucfirst($interaction['interaction_type'])
                ) ?>
                </strong>

                <br>

                <?= htmlspecialchars(
                    $interaction['interaction_subject']
                ) ?>

                <br><br>

                <?= nl2br(
                    htmlspecialchars(
                        $interaction['notes']
                    )
                ) ?>

                <br><br>

                <small>
                <?= date("m/d/Y h:i A", 
                    strtotime($interaction['interaction_date'])
                ) ?>
                </small>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</section>

<?php

include __DIR__ . '/../../../app/Shared/footer.php';