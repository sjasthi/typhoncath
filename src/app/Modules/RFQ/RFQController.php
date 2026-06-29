<?php
namespace App\Modules\RFQ;

class RFQController
{
    private RFQRepository $repo;
    private RFQService    $service;

    public function __construct()
    {
        $this->repo    = new RFQRepository();
        $this->service = new RFQService($this->repo);
    }

    // ── Pipeline board ────────────────────────────────────────────────────────

    public function index(): void
    {
        $grouped = $this->buildBoardGroups();
        extract($this->fetchList());
        [$winRateData, $valueByStage, $expiringQuotes] = $this->fetchAnalytics();

        include __DIR__ . '/views/pipeline_board.php';
    }

    // Groups all RFQs by stage to populate the kanban board columns.
    private function buildBoardGroups(): array
    {
        $grouped = array_fill_keys(RFQRepository::$stages, []);
        foreach ($this->repo->allForBoard() as $rfq) {
            $grouped[$rfq['stage']][] = $rfq;
        }
        return $grouped;
    }

    // Fetches the three analytics datasets displayed below the kanban board.
    private function fetchAnalytics(): array
    {
        return [
            $this->repo->winRateByAccount(),
            $this->repo->totalValueByStage(),
            $this->repo->quotesExpiringSoon(),
        ];
    }

    // Parses and validates GET parameters, then runs the paginated RFQ list query.
    private function fetchList(): array
    {
        $listSearch     = trim($_GET['q']        ?? '');
        $listIdSearch   = trim($_GET['id']       ?? '');
        $rawStages      = $_GET['stage']         ?? [];
        $listStages     = is_array($rawStages) ? $rawStages : [$rawStages];
        $listSort       = $_GET['sort']          ?? 'created_at';
        $listDir        = $_GET['dir']           ?? 'DESC';
        $rawPerPage     = $_GET['per_page']      ?? 25;
        $listShowAll    = $rawPerPage === 'all';
        $listPerPage    = $listShowAll ? PHP_INT_MAX : (in_array((int)$rawPerPage, [25, 50, 100]) ? (int)$rawPerPage : 25);
        $listPerPageVal = $listShowAll ? 'all' : $listPerPage;
        $listPage       = max(1, (int)($_GET['page'] ?? 1));
        $listTotal      = $this->repo->searchCount($listSearch, $listIdSearch, $listStages);
        $listPages      = $listShowAll ? 1 : (int)ceil($listTotal / $listPerPage);
        $listPage       = min($listPage, max(1, $listPages));
        $listRfqs       = $this->repo->search($listSearch, $listSort, $listDir, $listPerPage, ($listPage - 1) * $listPerPage, $listIdSearch, $listStages);

        return compact(
            'listSearch', 'listIdSearch', 'listStages', 'listSort', 'listDir',
            'listPerPage', 'listPerPageVal', 'listPage', 'listPages', 'listTotal', 'listRfqs'
        );
    }

    // ── Detail ────────────────────────────────────────────────────────────────

    public function show(int $id): void
    {
        $rfq = $this->repo->findById($id);

        if ($rfq === null) {
            http_response_code(404);
            echo '<section class="card"><h1>RFQ Not Found</h1><p class="text-muted">No RFQ exists with that ID.</p></section>';
            return;
        }

        $quotes       = $this->repo->getQuotesByRfqId($id);
        $reservations = $this->repo->getReservationsByRfqId($id);

        include __DIR__ . '/views/rfq_detail.php';
    }

    // ── Create RFQ ────────────────────────────────────────────────────────────

    private array $createErrors = [];
    private array $createInput  = [
        'title'                     => '',
        'account_id'                => '',
        'contact_id'                => '',
        'description'               => '',
        'stage'                     => 'New',
        'quote_amount'              => '',
        'quote_discount'            => '',
        'quote_validity_start_date' => '',
        'quote_validity_end_date'   => '',
    ];

    public function handleCreatePost(): void
    {
        $this->createInput = [
            'title'                     => trim($_POST['title']                      ?? ''),
            'account_id'                => trim($_POST['account_id']                 ?? ''),
            'contact_id'                => trim($_POST['contact_id']                 ?? ''),
            'description'               => trim($_POST['description']                ?? ''),
            'stage'                     => $_POST['stage']                           ?? 'New',
            'quote_amount'              => trim($_POST['quote_amount']               ?? ''),
            'quote_discount'            => trim($_POST['quote_discount']             ?? ''),
            'quote_validity_start_date' => trim($_POST['quote_validity_start_date']  ?? ''),
            'quote_validity_end_date'   => trim($_POST['quote_validity_end_date']    ?? ''),
        ];

        $this->createErrors = $this->service->validateRFQInput($this->createInput);

        if (empty($this->createErrors)) {
            $quoteAmount = trim($_POST['quote_amount'] ?? '');
            $quoteData   = is_numeric($quoteAmount) && $quoteAmount !== '' ? [
                'quote_amount'        => $quoteAmount,
                'discount'            => trim($_POST['quote_discount']            ?? ''),
                'validity_start_date' => trim($_POST['quote_validity_start_date'] ?? ''),
                'validity_end_date'   => trim($_POST['quote_validity_end_date']   ?? ''),
            ] : null;

            $reservations = [];
            $productIds   = $_POST['res_product_id']       ?? [];
            $quantities   = $_POST['res_quantity_reserved'] ?? [];
            foreach ($productIds as $i => $pid) {
                $pid = trim((string)$pid);
                $qty = trim((string)($quantities[$i] ?? ''));
                if ($pid !== '' && $qty !== '') {
                    $reservations[] = ['product_id' => $pid, 'quantity_reserved' => $qty];
                }
            }

            $rfqId = $this->service->createRFQ(
                $this->createInput,
                $quoteData,
                $reservations,
                (int)\App\Core\Auth::user()['id']
            );

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'RFQ "' . $this->createInput['title'] . '" created successfully.'];
            header('Location: /modules/rfq/pipeline.php');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not create RFQ — please fix the errors below.'];
    }

    public function create(): void
    {
        $accounts            = $this->repo->allAccounts();
        $contacts            = $this->repo->allContacts();
        $products            = $this->repo->allProducts();
        $stages              = RFQRepository::$stages;
        $quoteRequiredStages = RFQService::QUOTE_REQUIRED_STAGES;
        $errors              = $this->createErrors;
        $input               = $this->createInput;

        include __DIR__ . '/views/create_rfq.php';
    }

    // ── Edit RFQ ──────────────────────────────────────────────────────────────

    private array $editErrors = [];
    private array $editInput  = [
        'title'       => '',
        'account_id'  => '',
        'contact_id'  => '',
        'description' => '',
        'stage'       => 'New',
    ];

    public function handleUpdatePost(int $id): void
    {
        $this->editInput = [
            'title'       => trim($_POST['title']       ?? ''),
            'account_id'  => trim($_POST['account_id']  ?? ''),
            'contact_id'  => trim($_POST['contact_id']  ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'stage'       => $_POST['stage'] ?? 'New',
        ];

        $this->editErrors = $this->service->validateRFQInput($this->editInput);

        if (empty($this->editErrors)) {
            $this->service->updateRFQ($id, $this->editInput);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'RFQ updated successfully.'];
            header('Location: /modules/rfq/detail.php?id=' . $id);
            exit;
        }

        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not update RFQ — please fix the errors below.'];
    }

    public function edit(int $id): void
    {
        $rfq = $this->repo->findById($id);

        if ($rfq === null) {
            http_response_code(404);
            echo '<section class="card"><h1>RFQ Not Found</h1></section>';
            return;
        }

        if (empty($this->editErrors) && empty($_POST)) {
            $this->editInput = [
                'title'       => $rfq['title'],
                'account_id'  => $rfq['account_id'],
                'contact_id'  => $rfq['contact_id'] ?? '',
                'description' => $rfq['description'] ?? '',
                'stage'       => $rfq['stage'],
            ];
        }

        $accounts = $this->repo->allAccounts();
        $contacts = $this->repo->allContacts();
        $stages   = RFQRepository::$stages;
        $errors   = $this->editErrors;
        $input    = $this->editInput;

        include __DIR__ . '/views/edit_rfq.php';
    }

    // ── Delete RFQ ────────────────────────────────────────────────────────────

    public function handleDeletePost(int $id): void
    {
        $this->repo->delete($id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'RFQ deleted.'];
        header('Location: /modules/rfq/pipeline.php');
        exit;
    }

    // ── Stage change ──────────────────────────────────────────────────────────

    public function handleUpdateStagePost(int $id): void
    {
        $stage = $_POST['stage'] ?? '';

        if (!$this->service->isValidStage($stage)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid stage.'];
            header('Location: /modules/rfq/detail.php?id=' . $id);
            exit;
        }

        $this->service->changeStage($id, $stage);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Stage updated to "' . $stage . '".'];
        header('Location: /modules/rfq/detail.php?id=' . $id);
        exit;
    }

    // ── Add Quote ─────────────────────────────────────────────────────────────

    private array $quoteErrors = [];
    private array $quoteInput  = [
        'rfq_id'              => '',
        'quote_amount'        => '',
        'discount'            => '',
        'validity_start_date' => '',
        'validity_end_date'   => '',
    ];

    public function handleCreateQuotePost(): void
    {
        $this->quoteInput = [
            'rfq_id'              => trim($_POST['rfq_id']               ?? ''),
            'quote_amount'        => trim($_POST['quote_amount']         ?? ''),
            'discount'            => trim($_POST['discount']             ?? ''),
            'validity_start_date' => trim($_POST['validity_start_date']  ?? ''),
            'validity_end_date'   => trim($_POST['validity_end_date']    ?? ''),
        ];

        $this->quoteErrors = $this->service->validateQuoteInput($this->quoteInput);

        if (empty($this->quoteErrors)) {
            $rfqId = (int)$this->quoteInput['rfq_id'];
            $this->service->addQuote($rfqId, $this->quoteInput);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Quote added successfully.'];
            header('Location: /modules/rfq/detail.php?id=' . $rfqId);
            exit;
        }

        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not save quote — please fix the errors below.'];
    }

    public function createQuote(int $rfqId): void
    {
        $rfq    = $this->repo->findById($rfqId);
        $errors = $this->quoteErrors;
        $input  = $this->quoteInput['rfq_id'] !== '' ? $this->quoteInput : array_merge($this->quoteInput, ['rfq_id' => $rfqId]);

        if ($rfq === null) {
            http_response_code(404);
            echo '<section class="card"><h1>RFQ Not Found</h1></section>';
            return;
        }

        include __DIR__ . '/views/create_quote.php';
    }

    // ── Edit / Delete Quote ───────────────────────────────────────────────────

    private array $editQuoteErrors = [];
    private array $editQuoteInput  = [
        'rfq_id'              => '',
        'quote_amount'        => '',
        'discount'            => '',
        'validity_start_date' => '',
        'validity_end_date'   => '',
    ];

    public function handleEditQuotePost(int $quoteId): void
    {
        $this->editQuoteInput = [
            'rfq_id'              => trim($_POST['rfq_id']               ?? ''),
            'quote_amount'        => trim($_POST['quote_amount']         ?? ''),
            'discount'            => trim($_POST['discount']             ?? ''),
            'validity_start_date' => trim($_POST['validity_start_date']  ?? ''),
            'validity_end_date'   => trim($_POST['validity_end_date']    ?? ''),
        ];

        $errors = $this->service->validateQuoteInput($this->editQuoteInput);
        $rfqId  = (int)$this->editQuoteInput['rfq_id'];

        if (empty($errors)) {
            $this->repo->updateQuote($quoteId, $this->editQuoteInput);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Quote updated.'];
            header('Location: /modules/rfq/detail.php?id=' . $rfqId);
            exit;
        }

        $this->editQuoteErrors = $errors;
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please fix the errors below.'];
    }

    public function editQuote(int $quoteId): void
    {
        $quote = $this->repo->findQuoteById($quoteId);
        if (!$quote) {
            http_response_code(404);
            echo '<section class="card"><h1>Quote Not Found</h1></section>';
            return;
        }

        $rfq    = $this->repo->findById((int)$quote['rfq_id']);
        $errors = $this->editQuoteErrors;
        $input  = $this->editQuoteErrors ? $this->editQuoteInput : [
            'rfq_id'              => $quote['rfq_id'],
            'quote_amount'        => $quote['quote_amount'],
            'discount'            => $quote['discount'],
            'validity_start_date' => $quote['validity_start_date'] ?? '',
            'validity_end_date'   => $quote['validity_end_date']   ?? '',
        ];

        include __DIR__ . '/views/edit_quote.php';
    }

    public function handleDeleteQuotePost(int $quoteId): void
    {
        $rfqId = (int)($_POST['rfq_id'] ?? 0);
        $this->repo->deleteQuote($quoteId);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Quote deleted.'];
        header('Location: /modules/rfq/detail.php?id=' . $rfqId);
        exit;
    }

    // ── Reservation Status ────────────────────────────────────────────────────

    public function handleUpdateReservationStatusPost(int $reservationId): void
    {
        $status = trim($_POST['reservation_status'] ?? '');
        $rfqId  = (int)($_POST['rfq_id'] ?? 0);
        $this->repo->updateReservationStatus($reservationId, $status);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Reservation status updated.'];
        header('Location: /modules/rfq/detail.php?id=' . $rfqId);
        exit;
    }

    // ── Add Reservation ───────────────────────────────────────────────────────

    private array $reservationErrors = [];
    private array $reservationInput  = [
        'rfq_id'            => '',
        'product_id'        => '',
        'quantity_reserved' => '',
    ];

    public function handleCreateReservationPost(): void
    {
        $this->reservationInput = [
            'rfq_id'            => trim($_POST['rfq_id']            ?? ''),
            'product_id'        => trim($_POST['product_id']        ?? ''),
            'quantity_reserved' => trim($_POST['quantity_reserved'] ?? ''),
        ];

        $this->reservationErrors = $this->service->validateReservationInput($this->reservationInput);

        if (empty($this->reservationErrors)) {
            $rfqId = (int)$this->reservationInput['rfq_id'];
            $this->service->addReservation($rfqId, $this->reservationInput);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Inventory reservation added successfully.'];
            header('Location: /modules/rfq/detail.php?id=' . $rfqId);
            exit;
        }

        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not save reservation — please fix the errors below.'];
    }

    public function createReservation(int $rfqId): void
    {
        $rfq      = $this->repo->findById($rfqId);
        $products = $this->repo->allProducts();
        $errors   = $this->reservationErrors;
        $input    = $this->reservationInput['rfq_id'] !== '' ? $this->reservationInput : array_merge($this->reservationInput, ['rfq_id' => $rfqId]);

        if ($rfq === null) {
            http_response_code(404);
            echo '<section class="card"><h1>RFQ Not Found</h1></section>';
            return;
        }

        include __DIR__ . '/views/create_reservation.php';
    }
}
