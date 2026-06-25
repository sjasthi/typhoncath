<?php
$campaigns    = $campaigns    ?? [];
$listSearch   = $listSearch   ?? trim($_GET['q']      ?? '');
$listStatuses = $listStatuses ?? (array)($_GET['status'] ?? []);

$statusBadge = [
    'Draft'     => 'rfq-badge-neutral',
    'Scheduled' => 'rfq-badge-info',
    'Sent'      => 'rfq-badge-quoted',
    'Completed' => 'rfq-badge-success',
];
$typeBadge = [
    'Email'          => 'rfq-badge-info',
    'SMS Simulation' => 'rfq-badge-warning',
];
?>

<section class="card">
    <div class="module-header">
        <h1>Campaigns</h1>
        <a href="/modules/campaign/create.php" class="btn btn-primary">+ Create Campaign</a>
    </div>

    <!-- Search & status filters -->
    <form method="GET" action="" class="rfq-list-search-form">
        <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($listSearch) ?>"
            placeholder="Search by campaign name…"
            class="form-control rfq-list-search-input"
        >
        <div class="rfq-stage-checks">
            <?php foreach (['Draft', 'Scheduled', 'Sent', 'Completed'] as $s): ?>
            <label class="rfq-stage-check">
                <input type="checkbox" name="status[]" value="<?= $s ?>"
                    <?= in_array($s, $listStatuses, true) ? 'checked' : '' ?>>
                <?= $s ?>
            </label>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($listSearch !== '' || !empty($listStatuses)): ?>
            <a href="/modules/campaign/campaigns.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($campaigns)): ?>
        <p class="text-muted rfq-list-empty">
            No campaigns found.
            <a href="/modules/campaign/create.php">Create your first campaign →</a>
        </p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Sent</th>
                <th>Open Rate</th>
                <th>Click Rate</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($campaigns as $c): ?>
            <tr>
                <td class="rfq-list-id">#<?= (int)$c['id'] ?></td>
                <td>
                    <a href="/modules/campaign/detail.php?id=<?= (int)$c['id'] ?>">
                        <?= htmlspecialchars($c['campaign_name']) ?>
                    </a>
                </td>
                <td>
                    <span class="rfq-badge <?= $typeBadge[$c['campaign_type']] ?? 'rfq-badge-neutral' ?>">
                        <?= htmlspecialchars($c['campaign_type']) ?>
                    </span>
                </td>
                <td>
                    <span class="rfq-badge <?= $statusBadge[$c['status']] ?? 'rfq-badge-neutral' ?>">
                        <?= htmlspecialchars($c['status']) ?>
                    </span>
                </td>
                <td><?= (int)$c['sent_count'] ?></td>
                <td><?= $c['open_rate']  !== null ? number_format((float)$c['open_rate'],  1) . '%' : '—' ?></td>
                <td><?= $c['click_rate'] !== null ? number_format((float)$c['click_rate'], 1) . '%' : '—' ?></td>
                <td class="rfq-list-date"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php
    $totalPages = ($perPage > 0 && $totalCount > 0) ? (int)ceil($totalCount / $perPage) : 1;
    if ($totalPages > 1):
    ?>
    <div class="rfq-pagination">
        <?php if ($page > 1): ?>
            <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>"
               class="btn btn-secondary btn-sm">&#8249; Prev</a>
        <?php endif; ?>
        <span class="rfq-pagination-info">
            Page <?= $page ?> of <?= $totalPages ?>
            &mdash; <?= number_format($totalCount) ?> campaign<?= $totalCount !== 1 ? 's' : '' ?>
        </span>
        <?php if ($page < $totalPages): ?>
            <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>"
               class="btn btn-secondary btn-sm">Next &#8250;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</section>
