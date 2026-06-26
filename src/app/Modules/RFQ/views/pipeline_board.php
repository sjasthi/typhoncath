

<section class="card">
    <div class="rfq-list-toolbar">
        <h2 class="rfq-list-title">All RFQs</h2>
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

            <select name="per_page" class="form-control rfq-list-perpage-select">
                <?php foreach (['25' => '25 / page', '50' => '50 / page', '100' => '100 / page', 'all' => 'All'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= (string)$listPerPageVal === (string)$val ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
                <?php endforeach; ?>
            </select>

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

    <?php if ($listPages > 1): ?>
    <?php
        function rfqPageUrl(int $page, string $q, string $id, array $stages, string $sort, string $dir, string $perPage): string {
            $params = array_filter(['q' => $q, 'id' => $id, 'sort' => $sort, 'dir' => $dir, 'page' => $page], fn($v) => $v !== '');
            if (!empty($stages)) $params['stage'] = $stages;
            if ($perPage !== '25') $params['per_page'] = $perPage;
            return '?' . http_build_query($params);
        }

        function rfqPageNumbers(int $current, int $total): array {
            if ($total <= 7) return range(1, $total);
            $pages = [1];
            $start = max(2, $current - 2);
            $end   = min($total - 1, $current + 2);
            if ($start > 2)       $pages[] = '…';
            for ($i = $start; $i <= $end; $i++) $pages[] = $i;
            if ($end < $total - 1) $pages[] = '…';
            $pages[] = $total;
            return $pages;
        }
    ?>
    <div class="rfq-pagination">
        <?php if ($listPage > 1): ?>
            <a href="<?= rfqPageUrl($listPage - 1, $listSearch, $listIdSearch, $listStages, $listSort, $listDir, (string)$listPerPageVal) ?>" class="rfq-page-btn rfq-pagination-nav">&#8592; Prev</a>
        <?php else: ?>
            <span class="rfq-page-btn rfq-page-disabled">&#8592; Prev</span>
        <?php endif; ?>

        <?php foreach (rfqPageNumbers($listPage, $listPages) as $p): ?>
            <?php if ($p === '…'): ?>
                <span class="rfq-page-ellipsis">…</span>
            <?php elseif ($p === $listPage): ?>
                <span class="rfq-page-btn rfq-page-active"><?= $p ?></span>
            <?php else: ?>
                <a href="<?= rfqPageUrl((int)$p, $listSearch, $listIdSearch, $listStages, $listSort, $listDir, (string)$listPerPageVal) ?>" class="rfq-page-btn"><?= $p ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($listPage < $listPages): ?>
            <a href="<?= rfqPageUrl($listPage + 1, $listSearch, $listIdSearch, $listStages, $listSort, $listDir, (string)$listPerPageVal) ?>" class="rfq-page-btn rfq-pagination-nav">Next &#8594;</a>
        <?php else: ?>
            <span class="rfq-page-btn rfq-page-disabled">Next &#8594;</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="rfq-list-footer">
        <?php
            $from = $listTotal === 0 ? 0 : ($listPage - 1) * $listPerPage + 1;
            $to   = min($listPage * $listPerPage, $listTotal);
        ?>
        Showing <?= $from ?>–<?= $to ?> of <?= $listTotal ?> RFQ<?= $listTotal !== 1 ? 's' : '' ?>
        <?= $listSearch !== '' ? ' matching "' . htmlspecialchars($listSearch) . '"' : '' ?>
    </div>
</section>

<script src="/assets/js/rfq.js"></script>
