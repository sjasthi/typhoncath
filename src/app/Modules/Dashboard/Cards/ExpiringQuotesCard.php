<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (RFQ)
 * Preview card — quotes on active RFQs that are expiring soonest (or overdue).
 * Bounded top-N alert; each row links to its RFQ.
 */
class ExpiringQuotesCard extends DashboardCard
{
    private const LIMIT = 5;

    public function title(): string { return 'Expiring Quotes'; }

    public function permission(): ?string { return 'rfqs.view'; }

    public function body(): string
    {
        $rows = [];

        foreach ($this->service->expiringQuotes(self::LIMIT) as $r) {
            $days = (int)$r['days_remaining'];

            if ($days < 0) {
                $badge = abs($days) . 'd overdue';
                $class = 'rfq-badge-danger';
            } elseif ($days <= 7) {
                $badge = $days . 'd left';
                $class = 'rfq-badge-warning';
            } else {
                $badge = $days . 'd left';
                $class = 'rfq-badge-success';
            }

            $rows[] = [
                'label'       => $r['title'],
                'meta'        => ($r['account_name'] ?? '—') . ' · ' . $this->money($r['quote_amount']),
                'badge'       => $badge,
                'badge_class' => $class,
                'href'        => '/modules/rfq/detail.php?id=' . (int)$r['id'],
            ];
        }

        return $this->preview($rows, '/modules/rfq/pipeline.php', 'View pipeline');
    }
}
