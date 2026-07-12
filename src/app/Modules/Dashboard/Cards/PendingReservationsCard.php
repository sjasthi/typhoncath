<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * Stat card — how many reservations are still held (status 'Reserved', i.e. not
 * yet released or converted). A worklist count: stock committed to open RFQs
 * that still needs to be resolved.
 */
class PendingReservationsCard extends DashboardCard
{
    public function title(): string { return 'Pending Reservations'; }

    public function permission(): ?string { return 'inventory.view'; }

    public function body(): string
    {
        return $this->stat(
            $this->service->pendingReservationCount(),
            'Reservations awaiting release or conversion',
            '/modules/inventory/products.php?page=reservations',
            'View reservations'
        );
    }
}
