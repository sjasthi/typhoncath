<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (RFQ)
 * Stat card — count of RFQs not in a terminal stage.
 */
class ActiveRfqsCard extends DashboardCard
{
    public function title(): string { return 'Active RFQs'; }

    public function permission(): ?string { return 'rfqs.view'; }

    public function body(): string
    {
        // STUB: placeholder data. Replace with a real query, e.g.
        //   $counts = $this->repo->rfqStageCounts();
        //   $active = array of stages minus Won/Lost
        $active = 0;
        $won    = 0;
        $lost   = 0;

        return $this->stat(
            $active,
            "Open · {$won} won · {$lost} lost",
            '/modules/rfq/pipeline.php',
            'View pipeline'
        );
    }
}
