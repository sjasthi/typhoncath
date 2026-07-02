<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Max (Customer) — DROP-IN SLOT
 *
 * Preview card — the most recently logged customer interactions.
 *
 * TODO (Max): replace the stub rows with a real query. Add to DashboardRepository:
 *
 *   public function recentInteractions(int $limit = 5): array {
 *       // SELECT i.interaction_type, i.interaction_subject, a.account_name, i.interaction_date
 *       // FROM interactions i JOIN accounts a ON a.id = i.account_id
 *       // ORDER BY i.interaction_date DESC LIMIT ?
 *   }
 *
 * then map each row to:
 *   ['label' => interaction_subject, 'badge' => interaction_type, 'meta' => account_name]
 */
class RecentInteractionsCard extends DashboardCard
{
    public function title(): string { return 'Recent Interactions'; }

    public function permission(): ?string { return 'customers.view'; }

    public function body(): string
    {
        // STUB
        $rows = [
            ['label' => 'Intro call', 'badge' => 'Call', 'badge_class' => 'rfq-badge-info', 'meta' => 'Acme Co'],
        ];

        return $this->preview($rows, '/modules/customer/accounts.php', 'View accounts');
    }
}
