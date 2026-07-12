<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * Stat card — total units currently reserved across all active RFQ reservations.
 * Counts units (vs the Pending Reservations card, which counts reservation rows).
 */
class ReservedInventoryCard extends DashboardCard
{
    public function title(): string { return 'Reserved Inventory'; }

    public function permission(): ?string { return 'inventory.view'; }

    public function body(): string
    {
        return $this->stat(
            $this->service->reservedUnits(),
            'Units held for active RFQs',
            '/modules/inventory/products.php?page=reservations',
            'View reservations'
        );
    }
}
