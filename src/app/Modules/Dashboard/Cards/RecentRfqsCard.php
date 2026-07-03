<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (RFQ)
 * Preview card — the 5 most recently created RFQs.
 */
class RecentRfqsCard extends DashboardCard
{
    public function title(): string { return 'Recent RFQs'; }

    public function permission(): ?string { return 'rfqs.view'; }

    public function body(): string
    {
        // STUB: placeholder rows. Replace with a real query, e.g.
        //   foreach ($this->repo->recentRfqs(5) as $r) {
        //       $rows[] = ['label' => $r['title'], 'badge' => $r['stage'], 'meta' => $r['account_name']];
        //   }
        $rows = [
            ['label' => 'Sample RFQ A', 'badge' => 'New',    'badge_class' => 'rfq-badge-neutral', 'meta' => 'Acme Co'],
            ['label' => 'Sample RFQ B', 'badge' => 'Quoted', 'badge_class' => 'rfq-badge-quoted',  'meta' => 'Globex'],
        ];

        return $this->preview($rows, '/modules/rfq/pipeline.php', 'View all RFQs');
    }
}
