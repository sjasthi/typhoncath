<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * Preview card — products that have dropped to or below the low-stock threshold
 * (DashboardService::LOW_STOCK_THRESHOLD), lowest available first. These are the
 * SKUs due for reorder.
 */
class LowStockCard extends DashboardCard
{
    public function title(): string { return 'Low Stock'; }

    public function permission(): ?string { return 'inventory.view'; }

    public function body(): string
    {
        $rows = array_map(function (array $p): array {
            $available = (int) $p['available_quantity'];
            return [
                'label'       => $p['product_name'],
                'meta'        => $p['sku'],
                'badge'       => $available . ' left',
                'badge_class' => $available === 0 ? 'rfq-badge-danger' : 'rfq-badge-warning',
            ];
        }, $this->service->lowStockProducts());

        return $this->preview($rows, '/modules/inventory/products.php?low_stock=1', 'View inventory');
    }
}
