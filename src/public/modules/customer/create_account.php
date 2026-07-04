<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Customer\CustomerRepository;

Auth::requireLogin();

$repo     = new CustomerRepository();
$accounts = $repo->all();   // for the contact → account picker
$errors   = [];
$input     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $entityType = $_POST['entity_type'] ?? '';

    // Capture everything for re-populating the form on a validation error.
    $input = [
        'entity_type'  => $entityType,
        'account_name' => trim($_POST['account_name'] ?? ''),
        'email'        => trim($_POST['email']        ?? ''),
        'phone'        => trim($_POST['phone']        ?? ''),
        'address'      => trim($_POST['address']      ?? ''),
        'industry'     => trim($_POST['industry']     ?? ''),
        'source'       => trim($_POST['source']       ?? ''),
        'tags'         => trim($_POST['tags']         ?? ''),
        'account_id'   => trim($_POST['account_id']   ?? ''),
        'first_name'   => trim($_POST['first_name']   ?? ''),
        'last_name'    => trim($_POST['last_name']    ?? ''),
        'title'        => trim($_POST['title']        ?? ''),
    ];

    if ($entityType === 'account') {

        if ($input['account_name'] === '') {
            $errors[] = 'Account name is required.';
        }

        if (empty($errors)) {
            $repo->create([
                'account_name' => $input['account_name'],
                'email'        => $input['email'],
                'phone'        => $input['phone'],
                'address'      => $input['address'],
                'industry'     => $input['industry'],
                'source'       => $input['source'],
                'tags'         => $input['tags'],
            ]);
            header('Location: accounts.php');
            exit;
        }

    } elseif ($entityType === 'contact') {

        // Cardinality: a contact cannot exist without an account.
        if ($input['account_id'] === '') {
            $errors[] = 'Please select the account this contact belongs to.';
        }
        if ($input['first_name'] === '') {
            $errors[] = 'First name is required.';
        }
        if ($input['last_name'] === '') {
            $errors[] = 'Last name is required.';
        }

        if (empty($errors)) {
            $repo->createContact([
                'account_id' => (int)$input['account_id'],
                'first_name' => $input['first_name'],
                'last_name'  => $input['last_name'],
                'email'      => $input['email'],
                'phone'      => $input['phone'],
                'title'      => $input['title'],
            ]);
            header('Location: account_detail.php?id=' . (int)$input['account_id']);
            exit;
        }

    } else {
        $errors[] = 'Please choose whether to add an Account or a Contact.';
    }
}

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
include __DIR__ . '/../../../app/Modules/Customer/views/create_account.php';
include __DIR__ . '/../../../app/Shared/footer.php';
