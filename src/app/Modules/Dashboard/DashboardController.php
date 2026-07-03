<?php
namespace App\Modules\Dashboard;

class DashboardController
{
    private DashboardRepository $repo;

    public function __construct()
    {
        $this->repo = new DashboardRepository();
    }

    public function index(): void
    {
        // Build every card, then keep only the ones this user may see.
        $cards = array_filter($this->cards(), fn(DashboardCard $c) => $c->visible());

        include __DIR__ . '/views/dashboard.php';
    }

    /**
     * The dashboard card registry — order here is the order on the page.
     * To add a card: create a class under Cards/ extending DashboardCard,
     * then add it to this list.
     */
    private function cards(): array
    {
        return [
            new Cards\ActiveRfqsCard($this->repo),
            new Cards\ActiveCampaignsCard($this->repo),
            new Cards\TotalAccountsCard($this->repo),
            new Cards\ReservedInventoryCard($this->repo),
            new Cards\PipelineStageCard($this->repo),
            new Cards\RecentRfqsCard($this->repo),
            new Cards\LowStockCard($this->repo),
            new Cards\RecentInteractionsCard($this->repo),
        ];
    }
}
