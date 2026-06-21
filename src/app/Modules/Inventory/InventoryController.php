<?php
namespace App\Modules\Inventory;

class InventoryController
{
    private InventoryService $service;

    public function __construct()
    {
        $this->service = new InventoryService();
    }

    /**
     * GET /modules/inventory/products.php
     * Inventory List page — supports ?search= and ?low_stock=1
     */
    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $lowStockOnly = isset($_GET['low_stock']);

        $products = $this->service->getProductList(
            $search !== '' ? $search : null,
            $lowStockOnly
        );

        include __DIR__ . '/views/products_list.php';
    }

    /**
     * GET /modules/inventory/product_detail.php?id=X
     * GET /modules/inventory/product_detail.php  (no id = "add new" mode)
     */
    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $product = $id !== null ? $this->service->getProductDetail($id) : null;
        $error = null;

        include __DIR__ . '/views/product_detail.php';
    }

    /**
     * POST /modules/inventory/product_detail.php
     * Handles both create (no id) and update (id present).
     */
    public function save(): void
    {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $productName = trim($_POST['product_name'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '') ?: null;
        $startingQuantity = (int) ($_POST['available_quantity'] ?? 0);

        try {
            if ($id === null) {
                $newId = $this->service->createProduct($productName, $sku, $price, $description, $startingQuantity);
                header('Location: /modules/inventory/product_detail.php?id=' . $newId . '&saved=1');
                exit;
            }

            $this->service->updateProduct($id, $productName, $sku, $price, $description);
            header('Location: /modules/inventory/product_detail.php?id=' . $id . '&saved=1');
            exit;
        } catch (\InvalidArgumentException $e) {
            $product = $id !== null ? $this->service->getProductDetail($id) : null;
            $error = $e->getMessage();
            include __DIR__ . '/views/product_detail.php';
        }
    }

    /**
     * GET /modules/inventory/stock_update.php?id=X
     * Shows the stock update form for one product.
     */
    public function editStock(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->service->getProductDetail($id);
        $error = null;

        include __DIR__ . '/views/stock_update.php';
    }

    /**
     * POST /modules/inventory/stock_update.php
     * Applies a new available/reserved quantity for a product.
     */
    public function updateStock(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $availableQuantity = (int) ($_POST['available_quantity'] ?? 0);
        $reservedQuantity = (int) ($_POST['reserved_quantity'] ?? 0);

        try {
            $this->service->updateStock($productId, $availableQuantity, $reservedQuantity);
            header('Location: /modules/inventory/product_detail.php?id=' . $productId . '&saved=1');
            exit;
        } catch (\InvalidArgumentException $e) {
            $product = $this->service->getProductDetail($productId);
            $error = $e->getMessage();
            include __DIR__ . '/views/stock_update.php';
        }
    }

    /**
     * GET /modules/inventory/reservations.php
     * Shows all RFQ inventory reservations.
     */
    public function reservations(): void
    {
        $reservations = $this->service->getReservations();
        $error = $_GET['error'] ?? null;

        include __DIR__ . '/views/reservations.php';
    }

    /**
     * POST /modules/inventory/reservations.php
     * Handles releasing or converting a reservation via action param.
     */
    public function updateReservation(): void
    {
        $id = (int) ($_POST['reservation_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'release') {
                $this->service->releaseReservation($id);
            } elseif ($action === 'convert') {
                $this->service->convertReservation($id);
            } else {
                throw new \InvalidArgumentException('Unknown action.');
            }
            header('Location: /modules/inventory/reservations.php');
            exit;
        } catch (\Exception $e) {
            header('Location: /modules/inventory/reservations.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
