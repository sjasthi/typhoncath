<?php
/**
 * Dashboard view. Renders the permission-filtered cards passed in by
 * DashboardController::index() as $cards.
 *
 * Each $card is a DashboardCard; ->render() returns its full markup. The grid
 * auto-flows, so stat cards and preview cards can be mixed in any order.
 */
$cards = $cards ?? [];
?>
<section class="dashboard">
    <h1>Dashboard</h1>

    <?php if (empty($cards)): ?>
        <p class="text-muted">No dashboard cards are available for your role.</p>
    <?php else: ?>
    <div class="dash-grid">
        <?php foreach ($cards as $card): ?>
            <?= $card->render() ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
