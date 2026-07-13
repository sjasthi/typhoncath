<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Paginator — framework-agnostic pagination math.
 *
 * Knows nothing about SQL, HTML, or any particular module. A controller feeds
 * it the total row count plus the raw `per_page` and `page` request values; it
 * validates/whitelists them and exposes everything the query and the view need:
 *
 *   Controller:
 *     $total = $repo->countSomething($filters);
 *     $pager = new Paginator($total, $_GET['per_page'] ?? 25, $_GET['page'] ?? 1);
 *     $rows  = $repo->search(..., $pager->limit(), $pager->offset());
 *
 *   View (via the shared Shared/pagination.php partial):
 *     $pager->hasPages(), $pager->windowedNumbers(), $pager->from()/to() ...
 *
 * "Show all" is supported: pass per_page === 'all' (configurable token).
 */
final class Paginator
{
    /** Total number of matching rows (across all pages). */
    public readonly int $total;

    /** The current page, clamped to the range 1..pages. */
    public readonly int $page;

    /** Total number of pages (1 when showing all). */
    public readonly int $pages;

    /** True when the caller asked for every row on one page. */
    public readonly bool $showAll;

    /** The value to echo back into a per-page <select>/link: an int, or the "all" token. */
    public readonly int|string $perPageValue;

    /** The selectable page sizes (the "all" option is separate). Drives the shared per-page <select>. */
    public readonly array $allowed;

    /** The token that means "show every row". */
    public readonly string $allToken;

    /** Rows per page used for the LIMIT math (a large sentinel when showing all). */
    private int $perPage;

    /**
     * @param int        $total      Total matching rows (from a COUNT query).
     * @param int|string $perPageRaw Raw per_page request value.
     * @param int|string $pageRaw    Raw page request value.
     * @param int[]      $allowed    Whitelist of selectable page sizes.
     * @param int        $default    Fallback page size for invalid input.
     * @param string     $allToken   Sentinel meaning "show every row".
     */
    public function __construct(
        int $total,
        int|string $perPageRaw = 25,
        int|string $pageRaw = 1,
        array $allowed = [25, 50, 100],
        int $default = 25,
        string $allToken = 'all'
    ) {
        $this->total    = max(0, $total);
        $this->allowed  = $allowed;
        $this->allToken = $allToken;
        $this->showAll  = ((string) $perPageRaw === $allToken);

        if ($this->showAll) {
            $this->perPage      = PHP_INT_MAX;
            $this->perPageValue = $allToken;
            $this->pages        = 1;
        } else {
            $n = (int) $perPageRaw;
            $this->perPage      = in_array($n, $allowed, true) ? $n : $default;
            $this->perPageValue = $this->perPage;
            $this->pages        = (int) ceil($this->total / $this->perPage);
        }

        // Floor at 1, then cap at the last real page so ?page=9999 lands safely.
        $this->page = min(max(1, (int) $pageRaw), max(1, $this->pages));
    }

    /** LIMIT value for the SQL query. */
    public function limit(): int
    {
        return $this->perPage;
    }

    /** OFFSET value for the SQL query. */
    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /** Whether a pager should be shown at all (more than one page). */
    public function hasPages(): bool
    {
        return $this->pages > 1;
    }

    /** 1-based index of the first row shown on the current page (0 when empty). */
    public function from(): int
    {
        return $this->total === 0 ? 0 : ($this->page - 1) * $this->perPage + 1;
    }

    /** 1-based index of the last row shown on the current page. */
    public function to(): int
    {
        return (int) min($this->page * $this->perPage, $this->total);
    }

    /**
     * A windowed list of page numbers for the nav, with '…' gap markers, e.g.
     * [1, '…', 4, 5, 6, '…', 12]. Always shows the first and last page plus a
     * window of $around pages either side of the current one.
     *
     * @return array<int|string>
     */
    public function windowedNumbers(int $edge = 1, int $around = 2, int $threshold = 7): array
    {
        $total = $this->pages;
        if ($total <= $threshold) {
            return range(1, max(1, $total));
        }

        $numbers = [$edge];
        $start   = max($edge + 1, $this->page - $around);
        $end     = min($total - $edge, $this->page + $around);

        if ($start > $edge + 1) {
            $numbers[] = '…';
        }
        for ($i = $start; $i <= $end; $i++) {
            $numbers[] = $i;
        }
        if ($end < $total - $edge) {
            $numbers[] = '…';
        }
        $numbers[] = $total;

        return $numbers;
    }
}
