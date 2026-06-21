<?php
namespace App\Modules\Inventory;

use App\Core\Database;

class InventoryRepository
{
    /**
     * Get all products joined with their inventory counts.
     * Optionally filter by a search term (name or SKU) and/or low stock only.
     */
    public function all(?string $search = null, bool $lowStockOnly = false): array
    {
        $db = Database::connection();

        $sql = "SELECT p.id, p.product_name, p.sku, p.price, p.description,
                       i.available_quantity, i.reserved_quantity
                FROM products p
                LEFT JOIN inventory i ON i.product_id = p.id
                WHERE 1=1";

        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= " AND (p.product_name LIKE ? OR p.sku LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if ($lowStockOnly) {
            $sql .= " AND i.available_quantity < 10";
        }

        $sql .= " ORDER BY p.product_name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a single product (with inventory) by product id.
     */
    public function findById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT p.id, p.product_name, p.sku, p.price, p.description,
                    i.id AS inventory_id, i.available_quantity, i.reserved_quantity
             FROM products p
             LEFT JOIN inventory i ON i.product_id = p.id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Insert a new product and its starting inventory row.
     */
    public function create(string $productName, string $sku, float $price, ?string $description, int $availableQuantity): int
    {
        $db = Database::connection();

        $stmt = $db->prepare(
            "INSERT INTO products (product_name, sku, price, description)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$productName, $sku, $price, $description]);
        $productId = (int) $db->lastInsertId();

        $stmt = $db->prepare(
            "INSERT INTO inventory (product_id, available_quantity, reserved_quantity)
             VALUES (?, ?, 0)"
        );
        $stmt->execute([$productId, $availableQuantity]);

        return $productId;
    }

    /**
     * Update product details (name, sku, price, description).
     */
    public function updateProduct(int $id, string $productName, string $sku, float $price, ?string $description): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE products
             SET product_name = ?, sku = ?, price = ?, description = ?
             WHERE id = ?"
        );
        return $stmt->execute([$productName, $sku, $price, $description, $id]);
    }

    /**
     * Update available/reserved stock quantities for a product.
     */
    public function updateStock(int $productId, int $availableQuantity, int $reservedQuantity): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE inventory
             SET available_quantity = ?, reserved_quantity = ?
             WHERE product_id = ?"
        );
        return $stmt->execute([$availableQuantity, $reservedQuantity, $productId]);
    }

    /**
     * Get all reservations, joined with product and RFQ info.
     */
    public function allReservations(): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT r.id, r.rfq_id, r.product_id, r.quantity_reserved, r.reservation_status,
                    r.created_at, p.product_name, p.sku, rfq.title AS rfq_title
             FROM rfq_inventory_reservations r
             JOIN products p ON p.id = r.product_id
             JOIN rfqs rfq ON rfq.id = r.rfq_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a single reservation by id.
     */
    public function findReservationById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT r.*, p.product_name, p.sku
             FROM rfq_inventory_reservations r
             JOIN products p ON p.id = r.product_id
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new reservation row tied to an RFQ and product.
     */
    public function createReservation(int $rfqId, int $productId, int $quantity): int
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "INSERT INTO rfq_inventory_reservations (rfq_id, product_id, quantity_reserved, reservation_status)
             VALUES (?, ?, ?, 'Reserved')"
        );
        $stmt->execute([$rfqId, $productId, $quantity]);
        return (int) $db->lastInsertId();
    }

    /**
     * Update a reservation's status (Reserved / Released / Converted).
     */
    public function updateReservationStatus(int $id, string $status): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "UPDATE rfq_inventory_reservations SET reservation_status = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $id]);
    }
}
