<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (RFQ)
 * Preview card — total quoted RFQ value grouped by pipeline stage. The stage /
 * count / total-value aggregation is performed in SQL
 * (DashboardService::rfqValueByStage), not by loading RFQs into PHP.
 */
class RfqValueByStageCard extends DashboardCard
{
    public function title(): string { return 'RFQ Value by Stage'; }

    public function permission(): ?string { return 'rfqs.view'; }

    public function body(): string
    {
        $rows = [];

        foreach ($this->service->rfqValueByStage() as $r) {
            $count = (int)$r['rfq_count'];
            $value = (float)$r['total_value'];

            $rows[] = [
                'label'       => $r['stage'],
                'meta'        => $count . ' RFQ' . ($count === 1 ? '' : 's'),
                'badge'       => $value > 0 ? $this->money($value) : '—',
                'badge_class' => $this->stageBadgeClass($r['stage']),
            ];
        }

        return $this->preview($rows, '/modules/rfq/pipeline.php', 'View pipeline');
    }
}
