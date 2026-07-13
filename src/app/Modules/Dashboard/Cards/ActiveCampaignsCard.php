<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (Campaign)
 * Stat card — count of active campaigns (Scheduled or Sent), from the shared
 * single-pass campaign stats read.
 */
class ActiveCampaignsCard extends DashboardCard
{
    public function title(): string { return 'Active Campaigns'; }

    public function permission(): ?string { return 'campaigns.view'; }

    public function body(): string
    {
        return $this->stat(
            $this->service->activeCampaignCount(),
            'Scheduled or sent',
            '/modules/campaign/campaigns.php',
            'View campaigns'
        );
    }
}
