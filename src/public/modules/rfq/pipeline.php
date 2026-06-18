<?php
require_once __DIR__ . '/../../../app/Core/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();

include __DIR__ . '/../../../app/Shared/header.php';
include __DIR__ . '/../../../app/Shared/sidebar.php';
include __DIR__ . '/../../../app/Modules/RFQ/views/pipeline_board.php';
include __DIR__ . '/../../../app/Shared/footer.php';
