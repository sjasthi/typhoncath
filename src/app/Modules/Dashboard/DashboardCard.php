<?php
namespace App\Modules\Dashboard;

use App\Core\Permissions;

/**
 * Base class for every dashboard card.
 *
 * A card is a self-contained unit that appears on the dashboard grid. Each card
 * declares a title, an optional permission (so it only shows for roles that may
 * see it), and a body. The body is built with one of the two shared renderers:
 *
 *   - stat()    — a single big number for "how many" questions.
 *   - preview() — a truncated top-N list + a deep link for "which ones" questions.
 *
 * To add a card: extend this class, implement title() + body(), optionally set
 * permission(), then register it in DashboardController::cards().
 */
abstract class DashboardCard
{
    public function __construct(protected DashboardRepository $repo) {}

    /** Heading shown at the top of the card. */
    abstract public function title(): string;

    /** Inner HTML — build it with stat() or preview(). */
    abstract public function body(): string;

    /**
     * Permission key required to see this card. Return null for "always visible".
     * Uses the same keys as the rest of the app (e.g. 'rfqs.view').
     */
    public function permission(): ?string
    {
        return null;
    }

    /** Whether the current user is allowed to see this card. */
    public function visible(): bool
    {
        $perm = $this->permission();
        return $perm === null || Permissions::can($perm);
    }

    /** Full card markup: shell + title + body. */
    public function render(): string
    {
        return '<div class="dash-card">'
             . '<h3 class="dash-card-title">' . htmlspecialchars($this->title()) . '</h3>'
             . $this->body()
             . '</div>';
    }

    // ── Shared body renderers ─────────────────────────────────────────────────

    /**
     * A big single number with an optional sub-line and deep link.
     * Use for stat cards ("Active RFQs: 12").
     */
    protected function stat(int|string $value, string $sub = '', string $link = '', string $linkLabel = 'View all'): string
    {
        $html = '<p class="dash-stat">' . htmlspecialchars((string)$value) . '</p>';
        if ($sub !== '') {
            $html .= '<p class="dash-stat-sub text-muted">' . htmlspecialchars($sub) . '</p>';
        }
        if ($link !== '') {
            $html .= $this->deepLink($link, $linkLabel);
        }
        return $html;
    }

    /**
     * A truncated top-N list with a deep link to the full module page.
     * Use for preview cards ("Low Stock (4)" → 4 worst items → View inventory).
     *
     * @param array $rows Each row: [
     *     'label'       => string,          // primary text (required)
     *     'meta'        => string,          // muted secondary text (optional)
     *     'badge'       => string,          // badge text (optional)
     *     'badge_class' => string,          // rfq-badge-* class (optional)
     * ]
     */
    protected function preview(array $rows, string $link = '', string $linkLabel = 'View all'): string
    {
        if (empty($rows)) {
            $html = '<p class="dash-empty text-muted">Nothing to show.</p>';
        } else {
            $html = '<ul class="dash-list">';
            foreach ($rows as $row) {
                $html .= '<li class="dash-list-row">';
                $html .= '<span class="dash-list-label">' . htmlspecialchars($row['label'] ?? '') . '</span>';
                if (!empty($row['badge'])) {
                    $cls = htmlspecialchars($row['badge_class'] ?? 'rfq-badge-neutral');
                    $html .= '<span class="rfq-badge ' . $cls . '">' . htmlspecialchars($row['badge']) . '</span>';
                }
                if (!empty($row['meta'])) {
                    $html .= '<span class="dash-list-meta text-muted">' . htmlspecialchars($row['meta']) . '</span>';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        if ($link !== '') {
            $html .= $this->deepLink($link, $linkLabel);
        }
        return $html;
    }

    protected function deepLink(string $href, string $label = 'View all'): string
    {
        return '<a class="dash-card-link" href="' . htmlspecialchars($href) . '">'
             . htmlspecialchars($label) . ' &rarr;</a>';
    }
}
