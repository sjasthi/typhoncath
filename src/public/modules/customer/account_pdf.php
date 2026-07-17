<?php

require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../../../vendor/autoload.php';


Auth::requireLogin();


$accountId = $_GET['id'] ?? null;

if (!$accountId) {
    die("Missing account ID");
}


$db = Database::connection();


// -------------------------
// Load Account
// -------------------------

$stmt = $db->prepare("
    SELECT *
    FROM accounts
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$accountId]);

$account = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$account) {
    die("Account not found");
}



// -------------------------
// Load Contacts
// -------------------------

$stmt = $db->prepare("
    SELECT *
    FROM contacts
    WHERE account_id = ?
    ORDER BY first_name, last_name
");

$stmt->execute([$accountId]);

$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);



// -------------------------
// Load Interactions
// -------------------------

$stmt = $db->prepare("
    SELECT 
        i.*,
        u.name AS user_name
    FROM interactions i
    LEFT JOIN users u
        ON i.user_id = u.id
    WHERE i.account_id = ?
    ORDER BY i.interaction_date DESC
");

$stmt->execute([$accountId]);

$interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);



// -------------------------
// Build HTML
// -------------------------

$html = "

<html>
<head>

<style>

body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    color: #333;
}

h1 {
    text-align: center;
    font-size: 22px;
    margin-bottom: 25px;
}

h2 {
    background: #1f4e79;
    color: white;
    padding: 8px;
    font-size: 15px;
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th {
    background: #eeeeee;
    padding: 8px;
    text-align: left;
}

td {
    border: 1px solid #cccccc;
    padding: 8px;
}

.info td:first-child {
    width: 120px;
    font-weight: bold;
    background: #f5f5f5;
}

.interaction {
    margin-bottom: 15px;
}

.notes {
    margin-left: 20px;
    color: #555;
}

.footer {
    margin-top: 30px;
    font-size: 10px;
}

</style>

</head>

<body>


<h1>
TyphonCath CRM - Account Report
</h1>



<h2>
Account Information
</h2>


<table class='info'>

<tr>
<td>Company:</td>
<td>" . htmlspecialchars($account['account_name'] ?? 'N/A') . "</td>
</tr>


<tr>
<td>Email:</td>
<td>" . htmlspecialchars($account['email'] ?: 'N/A') . "</td>
</tr>


<tr>
<td>Phone:</td>
<td>" . htmlspecialchars($account['phone'] ?: 'N/A') . "</td>
</tr>


<tr>
<td>Address:</td>
<td>" . htmlspecialchars($account['address'] ?: 'N/A') . "</td>
</tr>


<tr>
<td>Industry:</td>
<td>" . htmlspecialchars($account['industry'] ?: 'N/A') . "</td>
</tr>


</table>





<h2>
Contacts
</h2>
";


if (count($contacts) > 0) {

    $html .= "

    <table>

    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
    </tr>

    ";


    foreach ($contacts as $contact) {

        $name = trim(
            ($contact['first_name'] ?? '') .
            " " .
            ($contact['last_name'] ?? '')
        );


        $html .= "

        <tr>

        <td>
        " . htmlspecialchars($name ?: 'Unnamed Contact') . "
        </td>

        <td>
        " . htmlspecialchars($contact['email'] ?: 'N/A') . "
        </td>

        <td>
        " . htmlspecialchars($contact['phone'] ?: 'N/A') . "
        </td>

        </tr>

        ";
    }


    $html .= "</table>";

} else {

    $html .= "<p>No contacts found.</p>";

}





$html .= "

<h2>
Interaction History
</h2>

";



if (count($interactions) > 0) {


    foreach ($interactions as $interaction) {


        $html .= "

        <div class='interaction'>

        <strong>
        "
        . htmlspecialchars($interaction['interaction_type'] ?? 'Note')
        .
        " - "
        .
        htmlspecialchars($interaction['interaction_subject'] ?? 'No Subject')
        .
        "
        </strong>

        <br>

        Date:
        "
        .
        htmlspecialchars($interaction['interaction_date'] ?? '')
        .
        "

        <br>

        User:
        "
        .
        htmlspecialchars($interaction['user_name'] ?? 'Unknown')
        .
        "
        ";


        if (!empty($interaction['notes'])) {

            $html .= "

            <div class='notes'>

            Notes:
            "
            .
            nl2br(htmlspecialchars($interaction['notes']))
            .
            "

            </div>

            ";
        }


        $html .= "</div>";

    }


} else {

    $html .= "<p>No interactions found.</p>";

}



$html .= "

<div class='footer'>

Generated:
"
.
date("m-d-Y")
.
"

</div>


</body>
</html>

";




// -------------------------
// Generate PDF
// -------------------------

$options = new Options();

$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);


$dompdf = new Dompdf($options);


$dompdf->loadHtml($html);

$dompdf->setPaper('letter', 'portrait');

$dompdf->render();



$cleanName = preg_replace(
    '/[^A-Za-z0-9]+/',
    '_',
    $account['account_name']
);

$cleanName = trim($cleanName, '_');


$filename =
    date("m-d-Y")
    .
    "_"
    .
    $cleanName
    .
    "_Account_Report.pdf";


$dompdf->stream($filename, [
    "Attachment" => false
]);

exit;