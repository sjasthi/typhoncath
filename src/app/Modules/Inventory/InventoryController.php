<?php
namespace App\Modules\Inventory;

// HTTP-facing entry points for the Inventory module (product CRUD, stock
// updates, and reservation actions). Routed from public/modules/inventory/products.php.
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
        $searchArg = $search !== '' ? $search : null;

        // Shared pagination. NOTE: this module already uses ?page= for routing
        // (detail/stock/delete), so the pagination page number rides on ?p= instead.
        $total    = $this->service->getProductCount($searchArg, $lowStockOnly);
        $pager    = new \App\Core\Paginator($total, $_GET['per_page'] ?? 25, $_GET['p'] ?? 1);
        $products = $this->service->getProductList($searchArg, $lowStockOnly, $pager->limit(), $pager->offset());

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
                $_SESSION['flash'] = ['type' => 'success', 'message' => "\"{$productName}\" was created successfully."];
                header('Location: /modules/inventory/products.php?page=detail&id=' . $newId);
                exit;
            }

            $this->service->updateProduct($id, $productName, $sku, $price, $description);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "\"{$productName}\" was saved successfully."];
            header('Location: /modules/inventory/products.php?page=detail&id=' . $id);
            exit;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            $back = $id !== null
                ? '/modules/inventory/products.php?page=detail&id=' . $id
                : '/modules/inventory/products.php?page=detail';
            header('Location: ' . $back);
            exit;
        } catch (\PDOException $e) {
            // Safety net: InventoryService::createProduct()/updateProduct() already
            // check for a duplicate SKU up front, but if a unique-constraint violation
            // (SQLSTATE 23000) reaches the database anyway — e.g. two submits at once —
            // show the same friendly, dismissible error instead of a raw 500.
            $message = $e->getCode() === '23000'
                ? 'That SKU is already in use by another product.'
                : 'Something went wrong while saving this product.';
            $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
            $back = $id !== null
                ? '/modules/inventory/products.php?page=detail&id=' . $id
                : '/modules/inventory/products.php?page=detail';
            header('Location: ' . $back);
            exit;
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

        try {
            $this->service->updateStock($productId, $availableQuantity);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Stock levels updated successfully.'];
            header('Location: /modules/inventory/products.php?page=detail&id=' . $productId);
            exit;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            header('Location: /modules/inventory/products.php?page=stock&id=' . $productId);
            exit;
        }
    }

    /**
     * GET ?page=delete&id=X
     * Shows a confirmation screen before deleting.
     */
    public function confirmDelete(): void
    {
        $id      = (int) ($_GET['id'] ?? 0);
        $product = $this->service->getProductDetail($id);

        if ($product === null) {
            http_response_code(404);
            echo '<section class="card"><h1>Product Not Found</h1><p class="text-muted">No product exists with that ID.</p><a href="/modules/inventory/products.php" class="btn mt-3">Back to Inventory</a></section>';
            return;
        }

        include __DIR__ . '/views/delete_confirm.php';
    }

    /**
     * POST ?page=delete
     * Deletes a product after checking for active reservations.
     */
    public function handleDelete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $product = $this->service->getProductDetail($id);
            $name    = $product['product_name'] ?? "Product #{$id}";
            $this->service->deleteProduct($id);
            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "\"{$name}\" was deleted successfully.",
            ];
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type'    => 'error',
                'message' => $e->getMessage(),
            ];
        }

        header('Location: /modules/inventory/products.php');
        exit;
    }

    /**
     * GET ?page=reservations
     * Shows all RFQ inventory reservations.
     */
    public function reservations(): void
    {
        $reservations = $this->service->getReservations();

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
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Reservation released.'];
            } elseif ($action === 'convert') {
                $this->service->convertReservation($id);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Reservation converted.'];
            } else {
                throw new \InvalidArgumentException('Unknown action.');
            }
            header('Location: /modules/inventory/products.php?page=reservations');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            header('Location: /modules/inventory/products.php?page=reservations');
            exit;
        }
    }
}
