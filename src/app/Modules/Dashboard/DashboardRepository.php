<?php
namespace App\Modules\Dashboard;

use App\Core\Database;

/**
 * Read-only aggregate queries that power the inventory dashboard cards.
 *
 * These live in the Dashboard module on purpose: they only ever SELECT from the
 * inventory tables (products / inventory / rfq_inventory_reservations) and never
 * write, so the dashboard can surface inventory health without reaching into or
 * modifying the Inventory module.
 */
class DashboardRepository
{
    /**
     * Products at or below a low-stock threshold, lowest available first.
     * @return array<int,array{product_name:string,sku:string,available_quantity:int}>
     */
    public function lowStock(int $threshold, int $limit = 5): array
    {
        $db    = Database::connection();
        $limit = max(1, $limit);
        $stmt  = $db->prepare(
            "SELECT p.product_name, p.sku, i.available_quantity
             FROM inventory i
             JOIN products p ON p.id = i.product_id
             WHERE i.available_quantity <= ?
             ORDER BY i.available_quantity ASC
             LIMIT {$limit}"
        );
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    /**
     * Products with the most units currently reserved, highest first.
     * @return array<int,array{product_name:string,sku:string,reserved_quantity:int}>
     */
    public function topReserved(int $limit = 5): array
    {
        $db    = Database::connection();
        $limit = max(1, $limit);
        $stmt  = $db->query(
            "SELECT p.product_name, p.sku, i.reserved_quantity
             FROM inventory i
             JOIN products p ON p.id = i.product_id
             WHERE i.reserved_quantity > 0
             ORDER BY i.reserved_quantity DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }

    /** Number of reservations still in the 'Reserved' (not released/converted) state. */
    public function pendingReservationCount(): int
    {
        $db = Database::connection();
        return (int) $db->query(
            "SELECT COUNT(*) FROM rfq_inventory_reservations
             WHERE reservation_status = 'Reserved'"
        )->fetchColumn();
    }

    /** Total units currently held across all active ('Reserved') reservations. */
    public function reservedUnits(): int
    {
        $db = Database::connection();
        return (int) $db->query(
            "SELECT COALESCE(SUM(quantity_reserved), 0) FROM rfq_inventory_reservations
             WHERE reservation_status = 'Reserved'"
        )->fetchColumn();
    }

    /**
     * Products where reserved units are the majority of total stock — i.e.
     * reserved / (available + reserved) exceeds $minRatio (e.g. 0.70 for 70%).
     * Highest reservation share first.
     *
     * @return array<int,array{product_name:string,sku:string,available_quantity:int,reserved_quantity:int,reserved_pct:float}>
     */
    public function heavilyReserved(float $minRatio, int $limit = 10): array
    {
        $db    = Database::connection();
        $limit = max(1, $limit);
        $stmt  = $db->prepare(
            "SELECT p.product_name, p.sku,
                    i.available_quantity, i.reserved_quantity,
                    i.reserved_quantity / (i.available_quantity + i.reserved_quantity) AS reserved_ratio
             FROM inventory i
             JOIN products p ON p.id = i.product_id
             WHERE (i.available_quantity + i.reserved_quantity) > 0
               AND i.reserved_quantity / (i.available_quantity + i.reserved_quantity) > ?
             ORDER BY reserved_ratio DESC
             LIMIT {$limit}"
        );
        $stmt->execute([$minRatio]);

        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['reserved_pct'] = round(((float) $row['reserved_ratio']) * 100);
        }
        return $rows;
    }
}
