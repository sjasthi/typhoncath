<section class="card">
    <h1>RFQ Pipeline Board</h1>


    <div class="rfq-main-view">
        <?php foreach ($grouped as $stage => $rfqs): ?>
        <div class="rfq-item">
            <div class="rfq-item-header">
                <span><?= htmlspecialchars($stage) ?></span>
                <span class="rfq-count"><?= count($rfqs) ?></span>
            </div>
            <?php foreach ($rfqs as $rfq): ?>
            <div class="rfq-card">
                <div class="rfq-card-title"><?= htmlspecialchars($rfq['title']) ?></div>
                <div class="rfq-card-meta">ID: #<?= $rfq['id'] ?> · <?= date('M j', strtotime($rfq['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<script src="/assets/js/rfq.js"></script>
