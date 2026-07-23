<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;
use App\Modules\Customer\CustomerRepository;

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
    public function body(): string
    {
        $repo = new CustomerRepository();

        $interactions = $repo->recentInteractions();

        $rows = [];

        foreach ($interactions as $interaction) {
            $rows[] = [
                'label' => $interaction['interaction_subject'],
                'badge' => ucfirst($interaction['interaction_type']),
                'badge_class' => $this->badgeClass($interaction['interaction_type']),
                'meta' => mb_strimwidth($interaction['account_name'], 0, 25, '...'),
                'date' => date('M j, g:i A', strtotime($interaction['interaction_date']))
            ];
        }

        return $this->preview(
            $rows,
            '/modules/customer/accounts.php',
            'View accounts'
        );
    }

    public function title(): string { return 'Recent Interactions'; }

    public function permission(): ?string { return 'customers.view'; }

    private function badgeClass(string $type): string
    {
        return match (strtolower($type)) {
            'call'    => 'rfq-badge-info',
            'email'   => 'rfq-badge-success',
            'meeting' => 'rfq-badge-warning',
            'note'    => 'rfq-badge-neutral',
            default   => 'rfq-badge-info',
        };
    }
}