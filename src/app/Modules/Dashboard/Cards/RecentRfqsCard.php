<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (RFQ)
 * Preview card — the 5 most recently updated RFQs, each linking to its detail
 * page, with account, stage badge, total quoted value, and date.
 */
class RecentRfqsCard extends DashboardCard
{
    private const LIMIT = 5;

    public function title(): string { return 'Recent RFQs'; }

    public function permission(): ?string { return 'rfqs.view'; }

    public function body(): string
    {
        $rows = [];

        foreach ($this->service->recentRfqs(self::LIMIT) as $r) {
            $value = (float)($r['total_value'] ?? 0);
            $meta  = $r['account_name'] ?? '—';
            if ($value > 0) {
                $meta .= ' · ' . $this->money($value);
            }

            $rows[] = [
                'label'       => '#' . (int)$r['id'] . ' ' . $r['title'],
                'meta'        => $meta,
                'badge'       => $r['stage'],
                'badge_class' => $this->stageBadgeClass($r['stage']),
                'date'        => date('M j, Y', strtotime($r['updated_at'])),
                'href'        => '/modules/rfq/detail.php?id=' . (int)$r['id'],
            ];
        }

        return $this->preview($rows, '/modules/rfq/pipeline.php', 'View all RFQs');
    }
}
