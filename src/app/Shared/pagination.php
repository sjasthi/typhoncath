<?php
/**
 * Shared pagination nav partial.
 *
 * Renders a windowed page navigation — «Prev · 1 … 4 5 6 … 12 · Next» — from a
 * Paginator, preserving the current query string (search, filters, sort,
 * per_page) so those survive a page click. Reusable by any module: the only
 * module-specific thing, the CSS class names, is overridable.
 *
 * Contract — set these in the including scope, then `include` this file:
 *   $pager             App\Core\Paginator   (required)
 *   $paginationClasses array                (optional CSS class overrides)
 *
 * Example (in a view already holding a $pager):
 *   $paginationClasses = ['container' => 'rfq-pagination', 'item' => 'rfq-page-btn', ...];
 *   include __DIR__ . '/../../../Shared/pagination.php';
 *
 * Renders nothing when there is only a single page.
 */

/** @var \App\Core\Paginator $pager */
if (!isset($pager) || !$pager->hasPages()) {
    return;
}

// Class names default to neutral, module-agnostic values; a module can override
// any subset to match its own stylesheet.
$pc = ($paginationClasses ?? []) + [
    'container' => 'pagination',
    'item'      => 'page-btn',
    'nav'       => 'pagination-nav',
    'disabled'  => 'page-disabled',
    'active'    => 'page-active',
    'ellipsis'  => 'page-ellipsis',
];

// Which query parameter carries the page number. Defaults to "page"; a module
// whose routing already uses "page" (e.g. Inventory's ?page=detail) can set a
// different one, e.g. $pageParam = 'p', before including this partial.
$pageParam = $pageParam ?? 'page';

// Build a URL for a target page, carrying over every current query parameter.
// http_build_query percent-encodes values, so the resulting href is safe.
$paginationUrl = static function (int $page) use ($pageParam): string {
    $params             = $_GET;
    $params[$pageParam] = $page;
    $params             = array_filter($params, static fn($v) => $v !== '' && $v !== []);
    return '?' . http_build_query($params);
};
?>
<div class="<?= $pc['container'] ?>">
    <?php if ($pager->page > 1): ?>
        <a href="<?= htmlspecialchars($paginationUrl($pager->page - 1)) ?>" class="<?= $pc['item'] . ' ' . $pc['nav'] ?>">&#8592; Prev</a>
    <?php else: ?>
        <span class="<?= $pc['item'] . ' ' . $pc['disabled'] ?>">&#8592; Prev</span>
    <?php endif; ?>

    <?php foreach ($pager->windowedNumbers() as $p): ?>
        <?php if ($p === '…'): ?>
            <span class="<?= $pc['ellipsis'] ?>">…</span>
        <?php elseif ($p === $pager->page): ?>
            <span class="<?= $pc['item'] . ' ' . $pc['active'] ?>"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= htmlspecialchars($paginationUrl((int) $p)) ?>" class="<?= $pc['item'] ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($pager->page < $pager->pages): ?>
        <a href="<?= htmlspecialchars($paginationUrl($pager->page + 1)) ?>" class="<?= $pc['item'] . ' ' . $pc['nav'] ?>">Next &#8594;</a>
    <?php else: ?>
        <span class="<?= $pc['item'] . ' ' . $pc['disabled'] ?>">Next &#8594;</span>
    <?php endif; ?>
</div>
