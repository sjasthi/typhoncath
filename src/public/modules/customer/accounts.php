<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Modules\Customer\CustomerRepository;

Auth::requireLogin();

$repo = new CustomerRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_account'])) {

        $repo->create([
            'account_name' => $_POST['account_name'],
            'email'        => $_POST['email'],
            'phone'        => $_POST['phone'],
            'address'      => $_POST['address'],
            'industry'     => $_POST['industry'],
            'source'       => $_POST['source'],
            'tags'         => $_POST['tags']
        ]);

        header('Location: accounts.php');
        exit;
    }

    if (isset($_POST['delete_account'])) {

        $repo->delete((int)$_POST['account_id']);

        header('Location: accounts.php');
        exit;
    }
}

$accounts = $repo->search(
    $_GET['search'] ?? '',
    $_GET['industry'] ?? '',
    $_GET['source'] ?? ''
);

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
include __DIR__ . '/../../../app/Modules/Customer/views/accounts_list.php';
include __DIR__ . '/../../../app/Shared/footer.php';
