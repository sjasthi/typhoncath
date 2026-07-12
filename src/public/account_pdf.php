<?php

require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;

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



$html = "
<h1>{$account['account_name']}</h1>

<h2>Customer Information</h2>

<table border='1' cellpadding='6'>
<tr><td>Email</td><td>{$account['email']}</td></tr>
<tr><td>Phone</td><td>{$account['phone']}</td></tr>
<tr><td>Address</td><td>{$account['address']}</td></tr>
<tr><td>Industry</td><td>{$account['industry']}</td></tr>
<tr><td>Source</td><td>{$account['source']}</td></tr>
<tr><td>Tags</td><td>{$account['tags']}</td></tr>
</table>


<h2>Contacts</h2>
";


foreach ($contacts as $c) {

    $html .= "
    <p>
    <strong>{$c['first_name']} {$c['last_name']}</strong><br>
    {$c['email']}<br>
    {$c['phone']}
    </p>
    ";

}



$html .= "<h2>Interaction History</h2>";


foreach ($interactions as $i) {

    $html .= "
    <p>
    <strong>{$i['interaction_type']}</strong><br>
    {$i['interaction_subject']}<br>
    {$i['notes']}<br>
    Date: {$i['interaction_date']}
    </p>
    <hr>
    ";

}



$pdf = new Dompdf();

$pdf->loadHtml($html);

$pdf->setPaper('letter');

$pdf->render();


$pdf->stream(
    $account['account_name'] . "_customer_report.pdf",
    [
        "Attachment" => true
    ]
);