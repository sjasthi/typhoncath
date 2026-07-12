<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (RFQ)
 * Preview card — the top accounts by win rate, computed in SQL as
 *   won / (won + lost) * 100
 * (open RFQs excluded from the denominator). Shows only the strongest few
 * accounts; "View all" links to the paginated win-rate drill-down page.
 */
class WinRateByAccountCard extends DashboardCard
{
    private const LIMIT = 5;

    public function title(): string { return 'Win Rate by Account'; }

    public function permission(): ?string { return 'rfqs.view'; }

    public function body(): string
    {
        $rows = [];

        foreach ($this->service->winRateByAccount(self::LIMIT) as $r) {
            $won    = (int)$r['won'];
            $lost   = (int)$r['lost'];
            $closed = (int)$r['closed'];
            $pct    = $r['win_rate_pct'];

            $rows[] = [
                'label'       => $r['account_name'],
                'meta'        => "{$won}W · {$lost}L · {$closed} closed",
                'badge'       => $pct !== null ? $pct . '%' : '—',
                'badge_class' => $pct !== null && (float)$pct >= 50 ? 'rfq-badge-success' : 'rfq-badge-danger',
            ];
        }

        return $this->preview($rows, '/modules/rfq/win_rate.php', 'View all accounts');
    }
}
