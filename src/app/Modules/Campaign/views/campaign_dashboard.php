<?php
$stats         = $stats         ?? [];
$upcoming      = $upcoming      ?? [];
$topPerformers = $topPerformers ?? [];
$reEngagement  = $reEngagement  ?? [];
$engagementGap = $engagementGap ?? [];
$momentum      = $momentum      ?? [];

$typeBadge = [
    'Email'          => 'rfq-badge-info',
    'SMS Simulation' => 'rfq-badge-warning',
];

function campDaysUntilBadge(int $days): string {
    if ($days <= 3)  return 'rfq-badge-danger';
    if ($days <= 7)  return 'rfq-badge-warning';
    if ($days <= 14) return 'rfq-badge-info';
    return 'rfq-badge-neutral';
}

function campGapBadge(float $gap): string {
    if ($gap <= 10) return 'rfq-badge-success';
    if ($gap <= 25) return 'rfq-badge-warning';
    return 'rfq-badge-danger';
}

function campRateBar(float $pct): string {
    $w     = min(100, max(0, $pct));
    $color = $pct >= 40 ? '#198754' : ($pct >= 20 ? '#b7791f' : '#1a56db');
    return '<span class="camp-rate-bar-wrap"><span class="camp-rate-bar" style="width:' . $w . '%;background:' . $color . '"></span>'
         . '<span class="camp-rate-val">' . number_format($pct, 1) . '%</span></span>';
}
?>

<!-- ── Stat Cards ─────────────────────────────────────────────────────────── -->
<div class="camp-stat-cards">
    <div class="camp-stat-card">
        <div class="camp-stat-label">Total Campaigns</div>
        <div class="camp-stat-value"><?= number_format((int)($stats['total'] ?? 0)) ?></div>
    </div>
    <div class="camp-stat-card">
        <div class="camp-stat-label">Scheduled</div>
        <div class="camp-stat-value camp-stat-scheduled"><?= (int)($stats['scheduled'] ?? 0) ?></div>
    </div>
    <div class="camp-stat-card">
        <div class="camp-stat-label">Sent / Completed</div>
        <div class="camp-stat-value camp-stat-sent"><?= (int)($stats['sent_completed'] ?? 0) ?></div>
    </div>
    <div class="camp-stat-card">
        <div class="camp-stat-label">Avg Open Rate</div>
        <div class="camp-stat-value">
            <?= $stats['avg_open_rate'] !== null ? number_format((float)$stats['avg_open_rate'], 1) . '%' : '—' ?>
        </div>
    </div>
    <div class="camp-stat-card">
        <div class="camp-stat-label">Avg Click Rate</div>
        <div class="camp-stat-value">
            <?= $stats['avg_click_rate'] !== null ? number_format((float)$stats['avg_click_rate'], 1) . '%' : '—' ?>
        </div>
    </div>
</div>

<!-- ── Upcoming Scheduled Sends ───────────────────────────────────────────── -->
<section class="card">
    <div class="module-header">
        <h2>Upcoming Scheduled Sends</h2>
        <a href="/modules/campaign/campaigns.php" class="btn btn-secondary btn-sm">View All Campaigns</a>
    </div>

    <?php if (empty($upcoming)): ?>
        <p class="text-muted rfq-list-empty">No scheduled campaigns upcoming.</p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Campaign</th>
                <th>Type</th>
                <th>Scheduled</th>
                <th>Days Until Send</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($upcoming as $row): ?>
            <?php $days = (int)$row['days_until']; ?>
            <tr>
                <td class="rfq-list-id">#<?= (int)$row['id'] ?></td>
                <td>
                    <a href="/modules/campaign/detail.php?id=<?= (int)$row['id'] ?>" class="rfq-list-link">
                        <?= htmlspecialchars($row['campaign_name']) ?>
                    </a>
                </td>
                <td>
                    <span class="rfq-badge <?= $typeBadge[$row['campaign_type']] ?? 'rfq-badge-neutral' ?>">
                        <?= htmlspecialchars($row['campaign_type']) ?>
                    </span>
                </td>
                <td class="rfq-list-date"><?= date('M j, Y g:i A', strtotime($row['scheduled_at'])) ?></td>
                <td>
                    <span class="rfq-badge <?= campDaysUntilBadge($days) ?>">
                        <?= $days === 0 ? 'Today' : ($days === 1 ? 'Tomorrow' : $days . ' days') ?>
                    </span>
                </td>
                <td class="text-muted"><?= htmlspecialchars($row['created_by_name'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<!-- ── Analytics Grid ────────────────────────────────────────────────────── -->
<section class="card">
    <div class="rfq-data-tables">

        <!-- Table 1: Most Successful Campaigns -->
        <div class="rfq-data-item">
            <h3>Most Successful Campaigns</h3>
            <div class="stage-filters">
                <button class="stage-filter-btn camp-type-btn active" data-type="all">All</button>
                <button class="stage-filter-btn camp-type-btn" data-type="Email">Email</button>
                <button class="stage-filter-btn camp-type-btn" data-type="SMS Simulation">SMS</button>
            </div>
            <?php if (empty($topPerformers)): ?>
                <p class="text-muted rfq-list-empty">No sent campaigns with metrics yet.</p>
            <?php else: ?>
            <table class="table" id="top-performers-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Campaign</th>
                        <th>Type</th>
                        <th>Sent</th>
                        <th>Open Rate</th>
                        <th>Click Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topPerformers as $i => $row): ?>
                    <tr data-type="<?= htmlspecialchars($row['campaign_type']) ?>">
                        <td class="rfq-list-id"><?= $i + 1 ?></td>
                        <td>
                            <a href="/modules/campaign/detail.php?id=<?= (int)$row['id'] ?>" class="rfq-list-link">
                                <?= htmlspecialchars($row['campaign_name']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="rfq-badge <?= $typeBadge[$row['campaign_type']] ?? 'rfq-badge-neutral' ?>">
                                <?= htmlspecialchars($row['campaign_type']) ?>
                            </span>
                        </td>
                        <td><?= number_format((int)$row['sent_count']) ?></td>
                        <td><?= campRateBar((float)$row['open_rate']) ?></td>
                        <td><?= $row['click_rate'] !== null ? campRateBar((float)$row['click_rate']) : '<span class="text-muted">—</span>' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Table 2: Re-engagement Candidates -->
        <div class="rfq-data-item">
            <h3>Re-engagement Candidates</h3>
            <p class="camp-data-desc text-muted">
                Contacts and accounts that were targeted by sent campaigns but never clicked through.
                The more cold campaigns, the higher the priority to re-engage.
            </p>
            <?php if (empty($reEngagement)): ?>
                <p class="text-muted rfq-list-empty">No cold recipients found — great engagement!</p>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Targeted</th>
                        <th>Zero-Click</th>
                        <th>Avg Open</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reEngagement as $row): ?>
                    <?php
                        $zeroPct   = (int)$row['campaigns_targeted'] > 0
                            ? round((int)$row['zero_click_campaigns'] / (int)$row['campaigns_targeted'] * 100)
                            : 0;
                        $coldBadge = (int)$row['zero_click_campaigns'] >= 3
                            ? 'rfq-badge-danger'
                            : ((int)$row['zero_click_campaigns'] >= 2 ? 'rfq-badge-warning' : 'rfq-badge-neutral');
                        $coldLabel = (int)$row['zero_click_campaigns'] >= 3 ? 'High' : ((int)$row['zero_click_campaigns'] >= 2 ? 'Medium' : 'Low');
                    ?>
                    <tr>
                        <td>
                            <span class="rfq-badge <?= $row['recipient_type'] === 'Contact' ? 'rfq-badge-info' : 'rfq-badge-quoted' ?>" style="margin-right:.35rem;font-size:.68rem">
                                <?= $row['recipient_type'] === 'Contact' ? 'C' : 'A' ?>
                            </span>
                            <?= htmlspecialchars($row['recipient_name'] ?? '—') ?>
                        </td>
                        <td><?= (int)$row['campaigns_targeted'] ?></td>
                        <td>
                            <?= (int)$row['zero_click_campaigns'] ?>
                            <span class="text-muted" style="font-size:.78rem">(<?= $zeroPct ?>%)</span>
                        </td>
                        <td><?= $row['avg_open_rate'] !== null ? number_format((float)$row['avg_open_rate'], 1) . '%' : '—' ?></td>
                        <td><span class="rfq-badge <?= $coldBadge ?>"><?= $coldLabel ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Table 3: Engagement Drop-off Analysis (surprise) -->
        <div class="rfq-data-item">
            <h3>Engagement Drop-off</h3>
            <p class="camp-data-desc text-muted">
                Campaigns with the biggest gap between open rate and click rate —
                great subject lines, but the content or CTA isn't converting.
            </p>
            <?php if (empty($engagementGap)): ?>
                <p class="text-muted rfq-list-empty">No sent campaigns with full metrics yet.</p>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Open %</th>
                        <th>Click %</th>
                        <th>Gap</th>
                        <th>CTR Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($engagementGap as $row): ?>
                    <?php
                        $gap      = (float)$row['engagement_gap'];
                        $ctrRatio = (float)$row['ctr_ratio'];
                    ?>
                    <tr>
                        <td>
                            <a href="/modules/campaign/detail.php?id=<?= (int)$row['id'] ?>" class="rfq-list-link">
                                <?= htmlspecialchars($row['campaign_name']) ?>
                            </a>
                        </td>
                        <td><?= number_format((float)$row['open_rate'],  1) ?>%</td>
                        <td><?= number_format((float)$row['click_rate'], 1) ?>%</td>
                        <td>
                            <span class="rfq-badge <?= campGapBadge($gap) ?>">
                                <?= number_format($gap, 1) ?>pt gap
                            </span>
                        </td>
                        <td>
                            <span class="rfq-badge <?= $ctrRatio >= 50 ? 'rfq-badge-success' : ($ctrRatio >= 25 ? 'rfq-badge-warning' : 'rfq-badge-danger') ?>">
                                <?= number_format($ctrRatio, 1) ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- ── Campaign Momentum Chart ────────────────────────────────────────────── -->
<section class="card">
    <div class="module-header">
        <h2>Campaign Momentum</h2>
        <div class="stage-filters" style="margin:0">
            <button class="stage-filter-btn camp-metric-btn active" data-metric="recipients">Recipients Reached</button>
            <button class="stage-filter-btn camp-metric-btn" data-metric="sent">Campaigns Sent</button>
            <button class="stage-filter-btn camp-metric-btn" data-metric="created">Campaigns Created</button>
        </div>
    </div>
    <?php if (empty($momentum)): ?>
        <p class="text-muted rfq-list-empty">No campaign data in the last 12 weeks.</p>
    <?php else: ?>
    <div style="position:relative;height:300px">
        <canvas id="momentum-chart"></canvas>
    </div>
    <script>
    window.__campMomentum = {
        labels:     <?= json_encode(array_values(array_column($momentum, 'week_label'))) ?>,
        recipients: <?= json_encode(array_values(array_map('intval',   array_column($momentum, 'total_recipients')))) ?>,
        sent:       <?= json_encode(array_values(array_map('intval',   array_column($momentum, 'campaigns_sent')))) ?>,
        created:    <?= json_encode(array_values(array_map('intval',   array_column($momentum, 'campaigns_created')))) ?>,
        openRate:  <?= json_encode(array_values(array_map(fn($v) => $v !== null ? (float)$v : null, array_column($momentum, 'avg_open_rate')))) ?>,
        clickRate: <?= json_encode(array_values(array_map(fn($v) => $v !== null ? (float)$v : null, array_column($momentum, 'avg_click_rate')))) ?>
    };
    </script>
    <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/assets/js/campaign.js"></script>
