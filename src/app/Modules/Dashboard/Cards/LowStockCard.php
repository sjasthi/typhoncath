<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Casey (Inventory) — DROP-IN SLOT
 *
 * Preview card — the products lowest on available stock.
 *
 * TODO (Casey): replace the stub rows below with a real query. Add a method to
 * DashboardRepository like:
 *
 *   public function lowStock(int $limit = 5): array {
 *       // SELECT p.product_name, p.sku, i.available_quantity
 *       // FROM inventory i JOIN products p ON p.id = i.product_id
 *       // ORDER BY i.available_quantity ASC LIMIT ?
 *   }
 *
 * then map each row to:
 *   ['label' => product_name, 'badge' => available_quantity . ' left', 'meta' => sku]
 */
class LowStockCard extends DashboardCard
{
    public function title(): string { return 'Low Stock'; }

    public function permission(): ?string { return 'inventory.view'; }

    public function body(): string
    {
        // STUB
        $rows = [
            ['label' => 'Sample Product', 'badge' => '2 left', 'badge_class' => 'rfq-badge-danger', 'meta' => 'SKU-000'],
        ];

        return $this->preview($rows, '/modules/inventory/products.php', 'View inventory');
    }
}
