<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * Preview card — the products with the most units currently reserved across
 * active RFQs. Answers "what stock is in demand right now?".
 */
class TopReservedProductsCard extends DashboardCard
{
    public function title(): string { return 'Top Reserved Products'; }

    public function permission(): ?string { return 'inventory.view'; }

    public function body(): string
    {
        $rows = array_map(fn (array $p): array => [
            'label'       => $p['product_name'],
            'meta'        => $p['sku'],
            'badge'       => (int) $p['reserved_quantity'] . ' reserved',
            'badge_class' => 'rfq-badge-info',
        ], $this->service->topReservedProducts());

        return $this->preview($rows, '/modules/inventory/products.php?page=reservations', 'View reservations');
    }
}
