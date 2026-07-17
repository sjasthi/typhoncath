<?php
/**
 * Shared DataTables assets. Include ONCE near the bottom of any list view that
 * renders a <table class="js-dt">. Loads (in dependency order) jQuery, DataTables
 * core + the Buttons extension, and the first-party tables.js initialiser — all
 * vendored locally under /assets/vendor (no CDN, no other dependencies).
 *
 * Guarded so multiple includes on one page emit the tags only once.
 */
if (defined('DT_ASSETS_INCLUDED')) {
    return;
}
define('DT_ASSETS_INCLUDED', true);
?>
<link rel="stylesheet" href="/assets/vendor/datatables/dataTables.dataTables.min.css">
<link rel="stylesheet" href="/assets/vendor/datatables/buttons.dataTables.min.css">
<style>
    table.js-dt { width: 100% !important; }
    /* Per-column filter row */
    table.js-dt thead tr.dt-filter th { padding: 4px 6px; }
    .dt-col-filter {
        width: 100%; box-sizing: border-box; padding: 4px 6px;
        font-size: 12px; font-weight: normal; border: 1px solid #ccc; border-radius: 4px;
    }
    /* Export buttons */
    .dt-export-btn.dt-button {
        background: #0d6efd; color: #fff; border: none; border-radius: 4px;
        padding: 5px 12px; margin-right: 4px; font-size: 13px;
    }
    .dt-export-btn.dt-button:hover { background: #0b5ed7; color: #fff; }
    /* "All" slow-load warning */
    .dt-all-warning {
        background: #fff3cd; color: #664d03; border: 1px solid #ffe69c;
        padding: 6px 10px; border-radius: 4px; margin-bottom: 10px; font-size: 13px;
    }
    .dt-container .dt-search { margin-bottom: 8px; }
</style>
<script src="/assets/vendor/jquery/jquery.min.js"></script>
<script src="/assets/vendor/datatables/dataTables.min.js"></script>
<script src="/assets/vendor/datatables/dataTables.buttons.min.js"></script>
<script src="/assets/js/tables.js"></script>
