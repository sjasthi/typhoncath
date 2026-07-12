<?php
namespace App\Modules\Dashboard;

class DashboardController
{
    private DashboardService $service;

    public function __construct()
    {
        $this->service = new DashboardService();
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
            // RFQ
            new Cards\ActiveRfqsCard($this->service),
            new Cards\RfqValueByStageCard($this->service),
            new Cards\RecentRfqsCard($this->service),
            new Cards\WinRateByAccountCard($this->service),
            new Cards\ExpiringQuotesCard($this->service),
            // Campaign
            new Cards\ActiveCampaignsCard($this->service),
            new Cards\CampaignPerformanceCard($this->service),
            new Cards\UpcomingCampaignSendsCard($this->service),
            // Other modules (owned elsewhere)
            new Cards\TotalAccountsCard($this->service),
            new Cards\ReservedInventoryCard($this->service),
            new Cards\LowStockCard($this->service),
            new Cards\RecentInteractionsCard($this->service),
        ];
    }
}
