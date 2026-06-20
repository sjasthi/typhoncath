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

        include __DIR__ . '/views/pipeline_board.php';
    }



    public function show(int $id): void{
        $rfq = $this->repo->findById($id);

        include __DIR__ . '/views/rfq_detail.php';
    }
}