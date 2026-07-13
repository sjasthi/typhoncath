<?php
namespace App\Modules\Inventory;

/**
 * File-backed store for per-product low-stock thresholds, keyed by product id.
 * Kept outside the database (storage/low_stock_thresholds.json) by project decision —
 * products/inventory tables are not touched for this feature.
 */
class LowStockThresholdStore
{
    public const DEFAULT_THRESHOLD = 10;

    private string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? __DIR__ . '/../../../storage/low_stock_thresholds.json';
    }

    public function get(int $productId): int
    {
        $all = $this->readAll();
        return $all[$productId] ?? self::DEFAULT_THRESHOLD;
    }

    /**
     * @param int[] $productIds
     * @return array<int,int> productId => threshold (defaulted where unset)
     */
    public function getMany(array $productIds): array
    {
        $all = $this->readAll();
        $result = [];
        foreach ($productIds as $id) {
            $result[$id] = $all[$id] ?? self::DEFAULT_THRESHOLD;
        }
        return $result;
    }

    public function set(int $productId, int $threshold): void
    {
        $all = $this->readAll();
        $all[$productId] = $threshold;
        $this->writeAll($all);
    }

    public function forget(int $productId): void
    {
        $all = $this->readAll();
        unset($all[$productId]);
        $this->writeAll($all);
    }

    /** @return array<int,int> */
    private function readAll(): array
    {
        if (!file_exists($this->path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($this->path) ?: '{}', true);
        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $id => $threshold) {
            $result[(int) $id] = (int) $threshold;
        }
        return $result;
    }

    /** @param array<int,int> $all */
    private function writeAll(array $all): void
    {
        $fp = fopen($this->path, 'c+');
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fwrite($fp, json_encode($all, JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}