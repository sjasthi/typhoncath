<section class="card">
    <h1>Inventory Reservations</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>RFQ</th>
                <th>Product</th>
                <th>Qty Reserved</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reservations)): ?>
                <tr>
                    <td colspan="6" class="text-muted">No reservations found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?= htmlspecialchars($reservation['rfq_title']) ?> (#<?= (int) $reservation['rfq_id'] ?>)</td>
                        <td><?= htmlspecialchars($reservation['product_name']) ?> (<?= htmlspecialchars($reservation['sku']) ?>)</td>
                        <td><?= (int) $reservation['quantity_reserved'] ?></td>
                        <td>
                            <?php
                                $statusColor = match ($reservation['reservation_status']) {
                                    'Reserved' => 'var(--tc-warning)',
                                    'Converted' => 'var(--tc-success)',
                                    'Released' => '#666',
                                    default => '#111',
                                };
                            ?>
                            <span style="color:<?= $statusColor ?>; font-weight:600;">
                                <?= htmlspecialchars($reservation['reservation_status']) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y', strtotime($reservation['created_at'])) ?></td>
                        <td>
                            <?php if ($reservation['reservation_status'] === 'Reserved'): ?>
                                <form method="POST" action="/modules/inventory/reservations.php" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?= (int) $reservation['id'] ?>">
                                    <input type="hidden" name="action" value="convert">
                                    <button type="submit" class="btn">Convert (Won)</button>
                                </form>
                                <form method="POST" action="/modules/inventory/reservations.php" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?= (int) $reservation['id'] ?>">
                                    <input type="hidden" name="action" value="release">
                                    <button type="submit" class="btn">Release (Lost)</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>
