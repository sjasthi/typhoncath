<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (RFQ)
 * Stat card — count + total quoted value of active RFQs (Quoted + Negotiation).
 * Totals are aggregated in SQL (DashboardService::activeRfqSummary); the card
 * never loads individual RFQ rows.
 */
class ActiveRfqsCard extends DashboardCard
{
    public function title(): string { return 'Active RFQs'; }

    public function permission(): ?string { return 'rfqs.view'; }

    public function body(): string
    {
        $summary = $this->service->activeRfqSummary();

        return $this->stat(
            $summary['count'],
            $this->money($summary['total_value']) . ' in Quoted / Negotiation',
            '/modules/rfq/pipeline.php?stage%5B%5D=Quoted&stage%5B%5D=Negotiation',
            'View pipeline'
        );
    }
}
