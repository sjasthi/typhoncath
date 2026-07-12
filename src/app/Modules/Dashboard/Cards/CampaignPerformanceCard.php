<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (Campaign)
 * Stat card — average open rate across delivered (Sent/Completed) campaigns,
 * with average click rate on the sub-line. Averages are computed in SQL and
 * shared with the Active Campaigns card via one campaign-stats read.
 */
class CampaignPerformanceCard extends DashboardCard
{
    public function title(): string { return 'Campaign Performance'; }

    public function permission(): ?string { return 'campaigns.view'; }

    public function body(): string
    {
        $perf = $this->service->campaignPerformance();

        if ($perf['sent_completed'] === 0 || $perf['avg_open'] === null) {
            return $this->stat(
                '—',
                'No delivered campaigns yet',
                '/modules/campaign/campaigns.php',
                'View campaigns'
            );
        }

        $click = $perf['avg_click'] !== null ? number_format($perf['avg_click'], 1) . '%' : '—';

        return $this->stat(
            number_format($perf['avg_open'], 1) . '%',
            'Avg open rate · ' . $click . ' avg click · ' . $perf['sent_completed'] . ' delivered',
            '/modules/campaign/campaigns.php',
            'View campaigns'
        );
    }
}
