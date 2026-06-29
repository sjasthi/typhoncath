<?php
namespace App\Modules\Inventory;

class InventoryService
{
    private InventoryRepository $repo;

    /** Quantity threshold under which a product is considered low stock. */
    private const LOW_STOCK_THRESHOLD = 10;

    public function __construct()
    {
        $this->repo = new InventoryRepository();
    }

    /**
     * Get the product list, optionally searched/filtered, with a
     * computed 'low_stock' flag added to each row for the view.
     */
    public function getProductList(?string $search = null, bool $lowStockOnly = false): array
    {
        $products = $this->repo->all($search, $lowStockOnly);

        foreach ($products as &$product) {
            $available = (int) ($product['available_quantity'] ?? 0);
            $product['low_stock'] = $available < self::LOW_STOCK_THRESHOLD;
        }

        return $products;
    }

    /**
     * Get a single product's detail, or null if it doesn't exist.
     */
    public function getProductDetail(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    /**
     * Business rule: validate and create a new product + starting stock.
     * Returns the new product id, or throws on invalid input.
     */
    public function createProduct(string $productName, string $sku, float $price, ?string $description, int $startingQuantity): int
    {
        if (trim($productName) === '') {
            throw new \InvalidArgumentException('Product name is required.');
        }
        if (trim($sku) === '') {
            throw new \InvalidArgumentException('SKU is required.');
        }
        if ($price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative.');
        }
        if ($startingQuantity < 0) {
            throw new \InvalidArgumentException('Starting quantity cannot be negative.');
        }

        return $this->repo->create($productName, $sku, $price, $description, $startingQuantity);
    }

    /**
     * Business rule: validate and apply product detail edits.
     */
    public function updateProduct(int $id, string $productName, string $sku, float $price, ?string $description): bool
    {
        if (trim($productName) === '') {
            throw new \InvalidArgumentException('Product name is required.');
        }
        if (trim($sku) === '') {
            throw new \InvalidArgumentException('SKU is required.');
        }
        if ($price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative.');
        }

        return $this->repo->updateProduct($id, $productName, $sku, $price, $description);
    }

    /**
     * Business rule: stock can never go negative, and reserved quantity
     * can never exceed available + reserved combined (i.e. you can't
     * reserve stock that doesn't exist).
     */
    public function updateStock(int $productId, int $availableQuantity, int $reservedQuantity): bool
    {
        if ($availableQuantity < 0 || $reservedQuantity < 0) {
            throw new \InvalidArgumentException('Stock quantities cannot be negative.');
        }

        return $this->repo->updateStock($productId, $availableQuantity, $reservedQuantity);
    }

    /**
     * Business rule: delete a product.
     * Blocked if the product has active (Reserved) reservations —
     * those must be released or converted first.
     */
    public function deleteProduct(int $id): bool
    {
        $active = $this->repo->countActiveReservations($id);
        if ($active > 0) {
            throw new \RuntimeException(
                "Cannot delete — this product has {$active} active reservation(s). " .
                "Release or convert them first."
            );
        }
        return $this->repo->delete($id);
    }

    /**
     * Get all inventory reservations, joined with product/RFQ info.
     */
    public function getReservations(): array
    {
        return $this->repo->allReservations();
    }

    /**
     * Business rule: reserving inventory for an RFQ.
     * - Cannot reserve more than what's currently available.
     * - On success, moves quantity from available -> reserved on the product,
     *   and creates a reservation record linked to the RFQ.
     */
    public function reserveForRfq(int $rfqId, int $productId, int $quantity): int
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Reservation quantity must be greater than zero.');
        }

        $product = $this->repo->findById($productId);
        if ($product === null) {
            throw new \InvalidArgumentException('Product not found.');
        }

        $available = (int) $product['available_quantity'];
        if ($quantity > $available) {
            throw new \RuntimeException('Cannot reserve more than the available quantity.');
        }

        $newAvailable = $available - $quantity;
        $newReserved = (int) $product['reserved_quantity'] + $quantity;
        $this->repo->updateStock($productId, $newAvailable, $newReserved);

        return $this->repo->createReservation($rfqId, $productId, $quantity);
    }

    /**
     * Business rule: releasing a reservation (RFQ was Lost or cancelled)
     * returns the reserved quantity back to available stock.
     */
    public function releaseReservation(int $reservationId): bool
    {
        $reservation = $this->repo->findReservationById($reservationId);
        if ($reservation === null) {
            throw new \InvalidArgumentException('Reservation not found.');
        }
        if ($reservation['reservation_status'] !== 'Reserved') {
            throw new \RuntimeException('Only active reservations can be released.');
        }

        $product = $this->repo->findById((int) $reservation['product_id']);
        $newAvailable = (int) $product['available_quantity'] + (int) $reservation['quantity_reserved'];
        $newReserved = (int) $product['reserved_quantity'] - (int) $reservation['quantity_reserved'];

        $this->repo->updateStock((int) $reservation['product_id'], $newAvailable, max(0, $newReserved));
        return $this->repo->updateReservationStatus($reservationId, 'Released');
    }

    /**
     * Business rule: converting a reservation (RFQ was Won) permanently
     * removes the stock from reserved (it has been sold/shipped), and
     * does NOT return it to available.
     */
    public function convertReservation(int $reservationId): bool
    {
        $reservation = $this->repo->findReservationById($reservationId);
        if ($reservation === null) {
            throw new \InvalidArgumentException('Reservation not found.');
        }
        if ($reservation['reservation_status'] !== 'Reserved') {
            throw new \RuntimeException('Only active reservations can be converted.');
        }

        $product = $this->repo->findById((int) $reservation['product_id']);
        $newReserved = (int) $product['reserved_quantity'] - (int) $reservation['quantity_reserved'];

        $this->repo->updateStock(
            (int) $reservation['product_id'],
            (int) $product['available_quantity'],
            max(0, $newReserved)
        );
        return $this->repo->updateReservationStatus($reservationId, 'Converted');
    }
}
