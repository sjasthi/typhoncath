<?php
namespace App\Modules\RFQ;

class RFQController
{   
    private RFQRepository $repo;
    
//create contructor of the repo, basic init function
    public function __construct(){
        $this->repo = new RFQRepository();
    }


// 
    public function index(): void{
        // THIS session calls repo.all() retuning all RFQs
        $rfqs = $this->repo->all();

        // Group rows by stage so the view can render each column separately
        $stages = ['New', 'In Review', 'Quoted', 'Negotiation', 'Won', 'Lost'];
        $grouped = array_fill_keys($stages, []);

        foreach ($rfqs as $rfq) {
            $grouped[$rfq['stage']][] = $rfq;
        }

        $winRateData    = $this->repo->winRateByAccount();
        $valueByStage   = $this->repo->totalValueByStage();
        $expiringQuotes = $this->repo->quotesExpiringSoon();

        $listSearch     = trim($_GET['q']        ?? '');
        $listIdSearch   = trim($_GET['id']       ?? '');
        $rawStages      = $_GET['stage']    ?? [];
        $listStages     = is_array($rawStages) ? $rawStages : [$rawStages];
        $listSort       = $_GET['sort']     ?? 'created_at';
        $listDir        = $_GET['dir']      ?? 'DESC';
        $rawPerPage     = $_GET['per_page'] ?? 25;
        $listShowAll    = $rawPerPage === 'all';
        $listPerPage    = $listShowAll ? PHP_INT_MAX : (in_array((int)$rawPerPage, [25, 50, 100]) ? (int)$rawPerPage : 25);
        $listPerPageVal = $listShowAll ? 'all' : $listPerPage;
        $listPage       = max(1, (int)($_GET['page'] ?? 1));
        $listTotal      = $this->repo->searchCount($listSearch, $listIdSearch, $listStages);
        $listPages      = $listShowAll ? 1 : (int)ceil($listTotal / $listPerPage);
        $listPage       = min($listPage, max(1, $listPages));
        $listRfqs       = $this->repo->search($listSearch, $listSort, $listDir, $listPerPage, ($listPage - 1) * $listPerPage, $listIdSearch, $listStages);

        include __DIR__ . '/views/pipeline_board.php';
    }



    public function show(int $id): void{
        $rfq = $this->repo->findById($id);

        include __DIR__ . '/views/rfq_detail.php';
    }

    public function list(){
        $rfqs = $this->repo->all();
        include __DIR__ . '/views/pipeline_board.php';
    }
}