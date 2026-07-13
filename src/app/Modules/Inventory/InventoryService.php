<?php
namespace App\Modules\Inventory;

// Business rules for the Inventory module: product validation, stock
// adjustments, and reservation lifecycle (reserve / release / convert).
class InventoryService
{
    private InventoryRepository $repo;
    private LowStockThresholdStore $thresholds;

    public function __construct()
    {
        $this->repo = new InventoryRepository();
        $this->thresholds = new LowStockThresholdStore();
    }

    /**
     * Get the product list, optionally searched/filtered, with each row's
     * per-product low_stock_threshold and a computed 'low_stock' flag added
     * for the view.
     */
    public function getProductList(?string $search = null, bool $lowStockOnly = false): array
    {
        $products = $this->repo->all($search);
        $productIds = array_map(fn($p) => (int) $p['id'], $products);
        $thresholds = $this->thresholds->getMany($productIds);

        foreach ($products as &$product) {
            $threshold = $thresholds[(int) $product['id']];
            $available = (int) ($product['available_quantity'] ?? 0);
            $product['low_stock_threshold'] = $threshold;
            $product['low_stock'] = $available < $threshold;
        }
        unset($product);

        if ($lowStockOnly) {
            $products = array_values(array_filter($products, fn($p) => $p['low_stock']));
        }

        return $products;
    }

    /**
     * Get a single product's detail (with its low-stock threshold), or null
     * if it doesn't exist.
     */
    public function getProductDetail(int $id): ?array
    {
        $product = $this->repo->findById($id);
        if ($product === null) {
            return null;
        }

        $product['low_stock_threshold'] = $this->thresholds->get($id);
        return $product;
    }

    /**
     * Business rule: validate and create a new product + starting stock.
     * Returns the new product id, or throws on invalid input.
     */
    public function createProduct(string $productName, string $sku, float $price, ?string $description, int $startingQuantity, int $lowStockThreshold = LowStockThresholdStore::DEFAULT_THRESHOLD): int
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
        if ($lowStockThreshold < 0) {
            throw new \InvalidArgumentException('Low stock threshold cannot be negative.');
        }
        if ($this->repo->findBySku($sku) !== null) {
            throw new \InvalidArgumentException("SKU \"{$sku}\" is already in use by another product.");
        }

        $productId = $this->repo->create($productName, $sku, $price, $description, $startingQuantity);
        $this->thresholds->set($productId, $lowStockThreshold);

        return $productId;
    }

    /**
     * Business rule: validate and apply product detail edits.
     */
    public function updateProduct(int $id, string $productName, string $sku, float $price, ?string $description, int $lowStockThreshold): bool
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
        if ($lowStockThreshold < 0) {
            throw new \InvalidArgumentException('Low stock threshold cannot be negative.');
        }
        $existing = $this->repo->findBySku($sku);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            throw new \InvalidArgumentException("SKU \"{$sku}\" is already in use by another product.");
        }

        $result = $this->repo->updateProduct($id, $productName, $sku, $price, $description);
        $this->thresholds->set($id, $lowStockThreshold);

        return $result;
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
        $result = $this->repo->delete($id);
        $this->thresholds->forget($id);

        return $result;
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
     *
     * TODO: once a backorder feature exists, this is the place to allow a
     * reservation to exceed available_quantity and flag the excess instead
     * of rejecting it outright.
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
