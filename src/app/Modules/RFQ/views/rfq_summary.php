
<section class="card">
    <div class="module-header">
        <h1>RFQ Summary</h1>
        <a href="/modules/rfq/pipeline.php" class="btn btn-secondary">View Pipeline</a>
    </div>

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


<section class="card">


    <div class="rfq-data-tables">

        <!-- Table 1: Win Rate by Account -->
        <div class="rfq-data-item">
            <h3>Win Rate by Account</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Won</th>
                        <th>Lost</th>
                        <th>Win Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($winRateData as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['account_name']) ?></td>
                        <td><?= (int)$row['won'] ?></td>
                        <td><?= (int)$row['lost'] ?></td>
                        <td>
                            <?php if ($row['win_rate_pct'] !== null): ?>
                                <?php $pct = (float)$row['win_rate_pct']; ?>
                                <span class="rfq-badge <?= $pct >= 50 ? 'rfq-badge-success' : 'rfq-badge-danger' ?>">
                                    <?= $pct ?>%
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Table 2: Total Value by Stage (client-side filter) -->
        <div class="rfq-data-item">
            <h3>Total RFQ Value by Stage</h3>
            <div class="stage-filters">
                <button class="stage-filter-btn active" data-stage="all">All</button>
                <?php foreach (['New', 'In Review', 'Quoted', 'Negotiation', 'Won', 'Lost'] as $s): ?>
                <button class="stage-filter-btn" data-stage="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></button>
                <?php endforeach; ?>
            </div>
            <table class="table" id="value-by-stage-table">
                <thead>
                    <tr>
                        <th>Stage</th>
                        <th>RFQs</th>
                        <th>Total Value</th>
                        <th>Avg Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($valueByStage as $row): ?>
                    <tr data-stage="<?= htmlspecialchars($row['stage']) ?>">
                        <td><?= htmlspecialchars($row['stage']) ?></td>
                        <td><?= (int)$row['rfq_count'] ?></td>
                        <td><?= $row['total_value'] > 0 ? '$' . number_format((float)$row['total_value']) : '—' ?></td>
                        <td><?= $row['avg_value'] > 0 ? '$' . number_format((float)$row['avg_value']) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Table 3: Quote Expiry Alerts -->
        <div class="rfq-data-item">
            <h3>Quote Expiry Alerts</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>RFQ</th>
                        <th>Account</th>
                        <th>Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expiringQuotes)): ?>
                    <tr><td colspan="4" class="text-muted">No active quotes.</td></tr>
                    <?php else: ?>
                    <?php foreach ($expiringQuotes as $row): ?>
                    <?php
                        $days = (int)$row['days_remaining'];
                        if ($days < 0) {
                            $badgeClass = 'rfq-badge-danger';
                            $label = abs($days) . 'd overdue';
                        } elseif ($days <= 7) {
                            $badgeClass = 'rfq-badge-warning';
                            $label = $days . 'd left';
                        } else {
                            $badgeClass = 'rfq-badge-success';
                            $label = $days . 'd left';
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['account_name']) ?></td>
                        <td>$<?= number_format((float)$row['quote_amount']) ?></td>
                        <td><span class="rfq-badge <?= $badgeClass ?>"><?= $label ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</section>



<script src="/assets/js/rfq.js"></script>
