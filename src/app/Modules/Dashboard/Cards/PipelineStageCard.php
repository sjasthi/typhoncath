<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (RFQ)
 * Preview card — count of RFQs per pipeline stage.
 */
class PipelineStageCard extends DashboardCard
{
    public function title(): string { return 'Pipeline by Stage'; }

    public function permission(): ?string { return 'rfqs.view'; }

    public function body(): string
    {
        // STUB: placeholder rows. Replace with a real query, e.g.
        //   foreach ($this->repo->rfqStageCounts() as $stage => $count) { ... }
        $rows = [
            ['label' => 'New',         'meta' => '0'],
            ['label' => 'In Review',   'meta' => '0'],
            ['label' => 'Quoted',      'meta' => '0'],
            ['label' => 'Negotiation', 'meta' => '0'],
        ];

        return $this->preview($rows, '/modules/rfq/pipeline.php', 'View pipeline');
    }
}
