<?php
/**
 * Shared per-page <select> for list pages.
 *
 * Renders the page-size chooser straight from the Paginator's own allowed sizes,
 * so the list of options ([25, 50, 100] + "All") lives in exactly one place
 * (App\Core\Paginator) instead of being duplicated in every module's view.
 *
 * Contract — set before include, inside the surrounding GET <form>:
 *   $pager             App\Core\Paginator  (required)
 *   $perPageClass      string              (optional CSS classes for the <select>)
 *   $perPageAutoSubmit bool                (optional; submit the form on change —
 *                                            handy on toolbars with no filter button)
 *
 * Submitting the form re-requests the list with the chosen `per_page`.
 */

/** @var \App\Core\Paginator $pager */
if (!isset($pager)) {
    return;
}
$perPageClass = $perPageClass ?? 'form-control';
$onChange     = !empty($perPageAutoSubmit) ? ' onchange="this.form.submit()"' : '';
?>
<select name="per_page" class="<?= $perPageClass ?>"<?= $onChange ?>>
    <?php foreach ($pager->allowed as $size): ?>
        <option value="<?= (int) $size ?>"<?= (string) $pager->perPageValue === (string) $size ? ' selected' : '' ?>><?= (int) $size ?> / page</option>
    <?php endforeach; ?>
    <option value="<?= htmlspecialchars($pager->allToken) ?>"<?= $pager->showAll ? ' selected' : '' ?>>All</option>
</select>
