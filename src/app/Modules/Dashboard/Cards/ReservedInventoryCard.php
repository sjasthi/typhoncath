<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Casey (Inventory) — DROP-IN SLOT
 *
 * Stat card — total units currently reserved across all RFQs.
 *
 * TODO (Casey): replace the stub with a real query. Add to DashboardRepository:
 *
 *   public function reservedUnits(): int {
 *       // SELECT COALESCE(SUM(quantity_reserved), 0)
 *       // FROM rfq_inventory_reservations WHERE reservation_status = 'Reserved'
 *   }
 */
class ReservedInventoryCard extends DashboardCard
{
    public function title(): string { return 'Reserved Inventory'; }

    public function permission(): ?string { return 'inventory.view'; }

    public function body(): string
    {
        // STUB
        $units = 0;

        return $this->stat(
            $units,
            'Units held for active RFQs',
            '/modules/inventory/products.php?page=reservations',
            'View reservations'
        );
    }
}
