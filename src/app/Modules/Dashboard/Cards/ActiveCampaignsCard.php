<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Trevor (Campaign)
 * Stat card — count of active campaigns.
 */
class ActiveCampaignsCard extends DashboardCard
{
    public function title(): string { return 'Active Campaigns'; }

    public function permission(): ?string { return 'campaigns.view'; }

    public function body(): string
    {
        // STUB: placeholder data. Replace with a real query, e.g.
        //   $active = $this->repo->activeCampaignCount();
        $active = 0;

        return $this->stat(
            $active,
            'Currently running',
            '/modules/campaign/campaigns.php',
            'View campaigns'
        );
    }
}
