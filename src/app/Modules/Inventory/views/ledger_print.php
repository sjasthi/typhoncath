<?php
// Bare, standalone printable document — no shared header/sidebar/footer chrome
// (see InventoryController::ledgerPrint() / public/modules/inventory/products.php).
// Every row matching the current filters is rendered (unpaginated), so what
// prints is the complete filtered record, not just one page of it.
$movementLabel = [
    'created'           => 'Created',
    'updated'           => 'Updated',
    'manual_adjustment' => 'Manual Adjustment',
    'reserved'          => 'Reserved',
    'released'          => 'Released',
    'converted'         => 'Converted',
    'deleted'           => 'Deleted',
];
$generatedBy = \App\Core\Auth::user()['name'] ?? 'Unknown';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Inventory Ledger<?= $product !== null ? ' — ' . htmlspecialchars($product['product_name']) : '' ?></title>
<style>
  body { font-family: -apple-system, Helvetica, Arial, sans-serif; margin: 2rem; color: #111; }
  h1 { margin: 0 0 0.25rem; font-size: 1.4rem; }
  .meta { color: #555; font-size: 0.85rem; margin-bottom: 1.5rem; line-height: 1.5; }
  table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
  th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
  th { background: #f3f4f6; }
  tbody tr:nth-child(even) { background: #fafafa; }
  .qty-pos { color: #166534; }
  .qty-neg { color: #b91c1c; }
  .print-toolbar { margin-bottom: 1.25rem; }
  .print-toolbar button {
      padding: 8px 18px; font-size: 0.95rem; cursor: pointer;
      background: #2563eb; color: #fff; border: none; border-radius: 4px;
  }
  @media print {
      .print-toolbar { display: none; }
      body { margin: 0.5in; }
      table { font-size: 0.75rem; }
  }
</style>
</head>
<body>
    <div class="print-toolbar">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>

    <h1>Inventory Ledger</h1>
    <div class="meta">
        <?php if ($product !== null): ?>
            Product: <strong><?= htmlspecialchars($product['product_name']) ?></strong> (<?= htmlspecialchars($product['sku']) ?>)<br>
        <?php endif; ?>
        Generated <?= date('M j, Y g:i A') ?> by <?= htmlspecialchars($generatedBy) ?><br>
        <?= count($movements) ?> event<?= count($movements) !== 1 ? 's' : '' ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Product</th>
                <th>SKU</th>
                <th>Event</th>
                <th>Qty &Delta;</th>
                <th>Available After</th>
                <th>Reserved After</th>
                <th>User</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movements)): ?>
                <tr><td colspan="9">No ledger entries found.</td></tr>
            <?php else: ?>
                <?php foreach ($movements as $m): ?>
                <tr>
                    <td><?= date('M j, Y g:i A', strtotime($m['created_at'])) ?></td>
                    <td><?= htmlspecialchars($m['product_name']) ?><?= $m['product_id'] === null ? ' (deleted)' : '' ?></td>
                    <td><?= htmlspecialchars($m['sku']) ?></td>
                    <td><?= htmlspecialchars($movementLabel[$m['movement_type']] ?? $m['movement_type']) ?></td>
                    <td>
                        <?php if ($m['quantity_delta'] === null): ?>
                            &mdash;
                        <?php elseif ((int)$m['quantity_delta'] > 0): ?>
                            <span class="qty-pos">+<?= (int)$m['quantity_delta'] ?></span>
                        <?php elseif ((int)$m['quantity_delta'] < 0): ?>
                            <span class="qty-neg"><?= (int)$m['quantity_delta'] ?></span>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                    </td>
                    <td><?= $m['available_quantity_after'] !== null ? (int)$m['available_quantity_after'] : '—' ?></td>
                    <td><?= $m['reserved_quantity_after'] !== null ? (int)$m['reserved_quantity_after'] : '—' ?></td>
                    <td><?= htmlspecialchars($m['user_name'] ?? 'System') ?></td>
                    <td><?= htmlspecialchars($m['note'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
