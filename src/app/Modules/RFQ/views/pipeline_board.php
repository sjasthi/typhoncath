

<section class="card">
    <div class="rfq-list-toolbar">
        <div class="module-header">
            <h2 class="rfq-list-title">All RFQs</h2>
            <a href="/modules/rfq/create.php" class="rfq-create-btn" title="Create RFQ">+</a>
        </div>
        
        <form method="GET" action="" class="rfq-list-search-form">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($listSort) ?>">
            <input type="hidden" name="dir"  value="<?= htmlspecialchars($listDir) ?>">
            

            
            <input
                type="number"
                name="id"
                value="<?= htmlspecialchars($listIdSearch) ?>"
                placeholder="ID #"
                class="form-control rfq-list-id-input"
                min="1"
            >

            <input
                type="text"
                name="q"
                value="<?= htmlspecialchars($listSearch) ?>"
                placeholder="Search title or account…"
                class="form-control rfq-list-search-input"
            >

            

            <div class="rfq-stage-checks">
                <?php foreach (App\Modules\RFQ\RFQRepository::$stages as $s): ?>
                <label class="rfq-stage-check rfq-stage-check--<?= strtolower(str_replace(' ', '-', $s)) ?>">
                    <input type="checkbox" name="stage[]" value="<?= htmlspecialchars($s) ?>"
                        <?= in_array($s, $listStages, true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($s) ?>
                </label>
                <?php endforeach; ?>
            </div>

            <?php
                // Page-size chooser — options come from the Paginator (single source of truth).
                $perPageClass = 'form-control rfq-list-perpage-select';
                include __DIR__ . '/../../../Shared/per_page_select.php';
            ?>

            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($listSearch !== '' || $listIdSearch !== '' || !empty($listStages)): ?>
                <a href="?sort=<?= urlencode($listSort) ?>&dir=<?= urlencode($listDir) ?>&per_page=<?= urlencode($listPerPageVal) ?>" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php
        $stageBadge = [
            'New'         => 'rfq-badge-neutral',
            'In Review'   => 'rfq-badge-info',
            'Quoted'      => 'rfq-badge-quoted',
            'Negotiation' => 'rfq-badge-warning',
            'Won'         => 'rfq-badge-success',
            'Lost'        => 'rfq-badge-danger',
        ];

        function rfqSortUrl(string $col, string $cur, string $dir, string $q, string $id, array $stages, string $perPage): string {
            $nextDir = ($cur === $col && $dir === 'ASC') ? 'DESC' : 'ASC';
            $params  = array_filter(['q' => $q, 'id' => $id, 'sort' => $col, 'dir' => $nextDir], fn($v) => $v !== '');
            if (!empty($stages)) $params['stage'] = $stages;
            if ($perPage !== '25') $params['per_page'] = $perPage;
            return '?' . http_build_query($params);
        }

        function rfqSortIcon(string $col, string $cur, string $dir): string {
            if ($cur !== $col) return '<span class="rfq-sort-icon rfq-sort-idle">↕</span>';
            return $dir === 'ASC'
                ? '<span class="rfq-sort-icon rfq-sort-asc">▲</span>'
                : '<span class="rfq-sort-icon rfq-sort-desc">▼</span>';
        }

        $cols = [
            'id'           => '#',
            'title'        => 'Title',
            'account_name' => 'Account',
            'stage'        => 'Stage',
            'created_at'   => 'Created',
            'updated_at'   => 'Updated',
        ];
    ?>

    <table class="table rfq-list-table">
        <thead>
            <tr>
                <?php foreach ($cols as $key => $label): ?>
                <th>
                    <a href="<?= rfqSortUrl($key, $listSort, $listDir, $listSearch, $listIdSearch, $listStages, (string)$listPerPageVal) ?>" class="rfq-sort-link">
                        <?= $label ?>
                        <?= rfqSortIcon($key, $listSort, $listDir) ?>
                    </a>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listRfqs)): ?>
            <tr>
                <td colspan="6" class="text-muted rfq-list-empty">
                    <?= $listSearch !== '' ? 'No RFQs match your search.' : 'No RFQs found.' ?>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($listRfqs as $row): ?>
            <tr>
                <td class="rfq-list-id">#<?= (int)$row['id'] ?></td>
                <td>
                    <a href="/modules/rfq/detail.php?id=<?= (int)$row['id'] ?>" class="rfq-list-link">
                        <?= htmlspecialchars($row['title']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($row['account_name'] ?? '—') ?></td>
                <td>
                    <span class="rfq-badge <?= $stageBadge[$row['stage']] ?? '' ?>">
                        <?= htmlspecialchars($row['stage']) ?>
                    </span>
                </td>
                <td class="text-muted rfq-list-date"><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                <td class="text-muted rfq-list-date"><?= date('M j, Y', strtotime($row['updated_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
        // Windowed pager rendered by the shared partial. The RFQ list keeps its
        // existing look by passing its own CSS class names; the math + markup
        // now live in App\Core\Paginator and Shared/pagination.php.
        $paginationClasses = [
            'container' => 'rfq-pagination',
            'item'      => 'rfq-page-btn',
            'nav'       => 'rfq-pagination-nav',
            'disabled'  => 'rfq-page-disabled',
            'active'    => 'rfq-page-active',
            'ellipsis'  => 'rfq-page-ellipsis',
        ];
        include __DIR__ . '/../../../Shared/pagination.php';
    ?>

    <div class="rfq-list-footer">
        Showing <?= $pager->from() ?>–<?= $pager->to() ?> of <?= $listTotal ?> RFQ<?= $listTotal !== 1 ? 's' : '' ?>
        <?= $listSearch !== '' ? ' matching "' . htmlspecialchars($listSearch) . '"' : '' ?>
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
