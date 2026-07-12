<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * Table card — products where reserved units are the majority of total stock
 * (reserved / (available + reserved) over DashboardService::HEAVILY_RESERVED_RATIO,
 * i.e. > 70%). These SKUs are nearly fully committed to open RFQs and at risk of
 * being unable to cover further demand. Rendered as a compact data table rather
 * than a preview list so the reserved/available split is visible at a glance.
 */
class HeavilyReservedCard extends DashboardCard
{
    public function title(): string { return 'Heavily Reserved (>70%)'; }

    public function permission(): ?string { return 'inventory.view'; }

    public function body(): string
    {
        $rows = $this->service->heavilyReservedProducts();

        if (empty($rows)) {
            return '<p class="dash-empty text-muted">No products are over 70% reserved.</p>';
        }

        $body = '';
        foreach ($rows as $r) {
            $pct = (int) $r['reserved_pct'];
            $body .= '<tr>'
                   . '<td>' . htmlspecialchars($r['product_name'])
                   . ' <span class="text-muted">' . htmlspecialchars($r['sku']) . '</span></td>'
                   . '<td><span class="rfq-badge rfq-badge-danger">' . $pct . '%</span></td>'
                   . '<td class="text-muted">' . (int) $r['reserved_quantity']
                   . ' / ' . (int) $r['available_quantity'] . '</td>'
                   . '</tr>';
        }

        return '<div style="overflow-x:auto;">'
             . '<table class="table rfq-list-table">'
             . '<thead><tr><th>Product</th><th>Reserved</th><th>Res / Avail</th></tr></thead>'
             . '<tbody>' . $body . '</tbody>'
             . '</table></div>'
             . $this->deepLink('/modules/inventory/products.php?page=reservations', 'View reservations');
    }
}
