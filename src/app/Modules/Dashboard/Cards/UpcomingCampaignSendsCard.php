<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (Campaign)
 * Preview card — campaigns scheduled to send soonest. Bounded top-N list from
 * upcomingScheduledSends(); each row links to its campaign.
 */
class UpcomingCampaignSendsCard extends DashboardCard
{
    private const LIMIT = 5;

    public function title(): string { return 'Upcoming Sends'; }

    public function permission(): ?string { return 'campaigns.view'; }

    public function body(): string
    {
        $rows = [];

        foreach ($this->service->upcomingCampaignSends(self::LIMIT) as $c) {
            $days = (int)$c['days_until'];
            if ($days <= 0) {
                $badge = 'Today';
                $class = 'rfq-badge-warning';
            } elseif ($days === 1) {
                $badge = 'Tomorrow';
                $class = 'rfq-badge-info';
            } else {
                $badge = 'in ' . $days . 'd';
                $class = 'rfq-badge-info';
            }

            $rows[] = [
                'label'       => $c['campaign_name'],
                'meta'        => $c['campaign_type'],
                'badge'       => $badge,
                'badge_class' => $class,
                'date'        => date('M j, g:i A', strtotime($c['scheduled_at'])),
                'href'        => '/modules/campaign/detail.php?id=' . (int)$c['id'],
            ];
        }

        return $this->preview($rows, '/modules/campaign/campaigns.php', 'View campaigns');
    }
}
