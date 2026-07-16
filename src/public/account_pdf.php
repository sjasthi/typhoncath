<?php

require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

require_once __DIR__ . '/../../../app/Core/PDF/SimplePDF.php';

Auth::requireLogin();

$id = (int)($_GET['id'] ?? 0);

$db = Database::connection();


$stmt = $db->prepare("
    SELECT *
    FROM accounts
    WHERE id = ?
");

$stmt->execute([$id]);

$account = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$account) {
    die("Customer not found");
}


$stmt = $db->prepare("
    SELECT *
    FROM contacts
    WHERE account_id = ?
");

$stmt->execute([$id]);

$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);



$stmt = $db->prepare("
    SELECT *
    FROM interactions
    WHERE account_id = ?
    ORDER BY interaction_date DESC
");

$stmt->execute([$id]);

$interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Create PDF
$pdf = new SimplePDF();

$y = 760;

$pdf->text(40, $y, "Customer Report");
$y -= 30;

$pdf->text(40, $y, "Account: " . $account['account_name']);
$y -= 20;

$pdf->text(40, $y, "Email: " . $account['email']);
$y -= 20;

$pdf->text(40, $y, "Phone: " . $account['phone']);
$y -= 20;

$pdf->text(40, $y, "Address: " . $account['address']);
$y -= 20;

$pdf->text(40, $y, "Industry: " . $account['industry']);
$y -= 20;

$pdf->text(40, $y, "Source: " . $account['source']);
$y -= 20;

$pdf->text(40, $y, "Tags: " . $account['tags']);

//Contacts
$y -= 40;

$pdf->text(40, $y, "Contacts");
$y -= 20;

foreach ($contacts as $contact) {

    $pdf->text(
        50,
        $y,
        $contact['first_name'] . " " .
        $contact['last_name']
    );

    $y -= 16;

    $pdf->text(60, $y, $contact['email']);
    $y -= 16;

    $pdf->text(60, $y, $contact['phone']);
    $y -= 24;
}

//Interactions
$y -= 20;

$pdf->text(40, $y, "Interaction History");
$y -= 20;

foreach ($interactions as $interaction) {

    $pdf->text(
        50,
        $y,
        "[" .
        $interaction['interaction_type'] .
        "] " .
        $interaction['interaction_subject']
    );

    $y -= 16;

    $pdf->text(
        60,
        $y,
        $interaction['notes']
    );

    $y -= 16;

    $pdf->text(
        60,
        $y,
        $interaction['interaction_date']
    );

    $y -= 28;
}

