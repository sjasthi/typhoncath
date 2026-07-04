<?php

require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

Auth::requireLogin();

$accountId = (int)($_GET['id'] ?? 0);
$editMode  = isset($_GET['edit']);

$db = Database::connection();

/*
|--------------------------------------------------------------------------
| UPDATE ACCOUNT
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {

    $stmt = $db->prepare("
        UPDATE accounts
        SET account_name=:account_name,
            email=:email,
            phone=:phone,
            address=:address,
            industry=:industry,
            source=:source,
            tags=:tags
        WHERE id=:id
    ");

    $stmt->execute([
        'account_name' => trim($_POST['account_name']),
        'email'        => trim($_POST['email']),
        'phone'        => trim($_POST['phone']),
        'address'      => trim($_POST['address']),
        'industry'     => trim($_POST['industry']),
        'source'       => trim($_POST['source']),
        'tags'         => trim($_POST['tags']),
        'id'           => $accountId
    ]);

    header("Location: account_detail.php?id=$accountId&updated=1");
    exit;
}

/*
|--------------------------------------------------------------------------
| ADD CONTACT
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact'])) {

    $stmt = $db->prepare("
        INSERT INTO contacts (
            account_id,
            first_name,
            last_name,
            email,
            phone
        )
        VALUES (
            :account_id,
            :first_name,
            :last_name,
            :email,
            :phone
        )
    ");

    $stmt->execute([
        'account_id' => $accountId,
        'first_name' => trim($_POST['first_name']),
        'last_name'  => trim($_POST['last_name']),
        'email'      => trim($_POST['email']),
        'phone'      => trim($_POST['phone'])
    ]);

    header("Location: account_detail.php?id=$accountId");
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE CONTACT
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {

    $stmt = $db->prepare("
        UPDATE contacts
        SET first_name=:first_name,
            last_name=:last_name,
            email=:email,
            phone=:phone
        WHERE id=:id AND account_id=:account_id
    ");

    $stmt->execute([
        'id'         => $_POST['contact_id'],
        'account_id' => $accountId,
        'first_name' => trim($_POST['first_name']),
        'last_name'  => trim($_POST['last_name']),
        'email'      => trim($_POST['email']),
        'phone'      => trim($_POST['phone'])
    ]);

    header("Location: account_detail.php?id=$accountId");
    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE CONTACT
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_contact'])) {

    $stmt = $db->prepare("
        DELETE FROM contacts
        WHERE id=:id AND account_id=:account_id
    ");

    $stmt->execute([
        'id' => $_POST['contact_id'],
        'account_id' => $accountId
    ]);

    header("Location: account_detail.php?id=$accountId");
    exit;
}

/*
|--------------------------------------------------------------------------
| ADD INTERACTION
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_interaction'])) {

    $stmt = $db->prepare("
        INSERT INTO interactions (
            account_id,
            user_id,
            interaction_type,
            interaction_date,
            interaction_subject,
            notes
        )
        VALUES (
            :account_id,
            :user_id,
            :interaction_type,
            NOW(),
            :interaction_subject,
            :notes
        )
    ");

    $stmt->execute([
        'account_id' => $accountId,
        'user_id' => $_SESSION['user']['id'],
        'interaction_type' => $_POST['interaction_type'] ?? '',
        'interaction_subject' => $_POST['subject'] ?? '',
        'notes' => $_POST['notes'] ?? ''
    ]);

    header("Location: account_detail.php?id=$accountId");
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE INTERACTION
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_interaction'])) {

    $stmt = $db->prepare("
        UPDATE interactions
        SET interaction_type=:type,
            interaction_subject=:subject,
            notes=:notes
        WHERE id=:id AND account_id=:account_id
    ");

    $stmt->execute([
        'type' => $_POST['interaction_type'],
        'subject' => $_POST['subject'],
        'notes' => $_POST['notes'],
        'id' => $_POST['interaction_id'],
        'account_id' => $accountId
    ]);

    header("Location: account_detail.php?id=$accountId");
    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE INTERACTION
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_interaction'])) {

    $stmt = $db->prepare("
        DELETE FROM interactions
        WHERE id=:id AND account_id=:account_id
    ");

    $stmt->execute([
        'id' => $_POST['interaction_id'],
        'account_id' => $accountId
    ]);

    header("Location: account_detail.php?id=$accountId");
    exit;
}

/*
|--------------------------------------------------------------------------
| LOAD DATA
|--------------------------------------------------------------------------
*/
$stmt = $db->prepare("SELECT * FROM accounts WHERE id=:id");
$stmt->execute(['id'=>$accountId]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) die("Account not found");

$stmt = $db->prepare("SELECT * FROM contacts WHERE account_id=:id ORDER BY last_name");
$stmt->execute(['id'=>$accountId]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM interactions WHERE account_id=:id ORDER BY interaction_date DESC");
$stmt->execute(['id'=>$accountId]);
$interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
?>

<style>
.card-box {
    border:1px solid #ddd;
    padding:10px;
    margin-bottom:10px;
    border-radius:6px;
}
.inline-actions {
    display:flex;
    gap:10px;
    margin-top:8px;
}
</style>

<section class="card">

<h1><?= htmlspecialchars($account['account_name']) ?></h1>

<a href="accounts.php">← Back</a>

<?php if (!$editMode): ?>
    <a class="btn btn-primary" href="?id=<?= $accountId ?>&edit=1">Edit Account</a>
<?php endif; ?>

<form method="POST">

<table class="table">

<?php
function field($label,$name,$value,$editMode){
    echo "<tr><th>$label</th><td>";

    if ($editMode) {
        echo "<input name='$name' value='".htmlspecialchars($value)."' style='width:100%'>";
    } else {
        echo htmlspecialchars($value);
    }

    echo "</td></tr>";
}

field("Account Name","account_name",$account['account_name'],$editMode);
field("Email","email",$account['email'],$editMode);
field("Phone","phone",$account['phone'],$editMode);
field("Address","address",$account['address'],$editMode);
field("Industry","industry",$account['industry'],$editMode);
field("Source","source",$account['source'],$editMode);
field("Tags","tags",$account['tags'],$editMode);
?>

</table>

<?php if ($editMode): ?>
<button name="update_account" class="btn btn-primary">Save</button>
<?php endif; ?>

</form>

</section>

<!-- CONTACTS -->
<section class="card">

<h2>Contacts</h2>

<!-- ADD CONTACT -->
<form method="POST">
    <input name="first_name" placeholder="First Name" required>
    <input name="last_name" placeholder="Last Name" required>
    <input name="email" placeholder="Email">
    <input name="phone" placeholder="Phone">
    <button name="add_contact" class="btn btn-primary">Add Contact</button>
</form>

<br>

<?php foreach ($contacts as $c): ?>

<div class="card-box">

<?php if (isset($_GET['edit_contact']) && $_GET['edit_contact'] == $c['id']): ?>

<form method="POST">
    <input type="hidden" name="contact_id" value="<?= $c['id'] ?>">

    <input name="first_name" value="<?= htmlspecialchars($c['first_name']) ?>">
    <input name="last_name" value="<?= htmlspecialchars($c['last_name']) ?>">
    <input name="email" value="<?= htmlspecialchars($c['email']) ?>">
    <input name="phone" value="<?= htmlspecialchars($c['phone']) ?>">

    <button name="update_contact">Save</button>
    <a href="?id=<?= $accountId ?>">Cancel</a>
</form>

<?php else: ?>

<strong><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?></strong>
<div><?= htmlspecialchars($c['email']) ?></div>
<div><?= htmlspecialchars($c['phone']) ?></div>

<div class="inline-actions">

    <a href="?id=<?= $accountId ?>&edit_contact=<?= $c['id'] ?>">✏️ Edit</a>

    <form method="POST" style="display:inline;">
        <input type="hidden" name="contact_id" value="<?= $c['id'] ?>">
        <button name="delete_contact" onclick="return confirm('Delete contact?')">🗑 Delete</button>
    </form>

</div>

<?php endif; ?>

</div>

<?php endforeach; ?>

</section>

<!-- INTERACTIONS -->
<section class="card">

<h2>Log Interaction</h2>

<form method="POST">

<input name="subject" placeholder="Subject" style="width:100%;margin-bottom:10px;">

<select name="interaction_type">
    <option>Call</option>
    <option>Email</option>
    <option>Meeting</option>
    <option>Note</option>
</select>

<br><br>

<textarea name="notes" style="width:100%;height:120px;"></textarea>

<br><br>

<button name="add_interaction" class="btn btn-primary">Save Interaction</button>

</form>

<br>

<h2>Interaction History</h2>

<?php foreach ($interactions as $i): ?>

<div class="card-box">

<?php if (isset($_GET['edit_interaction']) && $_GET['edit_interaction'] == $i['id']): ?>

<form method="POST">
    <input type="hidden" name="interaction_id" value="<?= $i['id'] ?>">

    <input name="subject" value="<?= htmlspecialchars($i['interaction_subject']) ?>">

    <select name="interaction_type">
        <option <?= $i['interaction_type']=='Call'?'selected':'' ?>>Call</option>
        <option <?= $i['interaction_type']=='Email'?'selected':'' ?>>Email</option>
        <option <?= $i['interaction_type']=='Meeting'?'selected':'' ?>>Meeting</option>
        <option <?= $i['interaction_type']=='Note'?'selected':'' ?>>Note</option>
    </select>

    <textarea name="notes"><?= htmlspecialchars($i['notes']) ?></textarea>

    <button name="update_interaction">Save</button>
    <a href="?id=<?= $accountId ?>">Cancel</a>
</form>

<?php else: ?>

<strong><?= htmlspecialchars($i['interaction_type']) ?></strong>
<div><?= htmlspecialchars($i['interaction_subject']) ?></div>
<div><?= nl2br(htmlspecialchars($i['notes'])) ?></div>

<div class="inline-actions">

    <a href="?id=<?= $accountId ?>&edit_interaction=<?= $i['id'] ?>">✏️ Edit</a>

    <form method="POST" style="display:inline;">
        <input type="hidden" name="interaction_id" value="<?= $i['id'] ?>">
        <button name="delete_interaction" onclick="return confirm('Delete interaction?')">🗑 Delete</button>
    </form>

</div>

<?php endif; ?>

</div>

<?php endforeach; ?>

</section>

<?php include __DIR__ . '/../../../app/Shared/footer.php'; ?>