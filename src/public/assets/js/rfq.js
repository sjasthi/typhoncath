// RFQ / Pipeline module JavaScript

function getAllRFQ(){

};

// Preserve scroll position across search/sort form submissions
(function () {
    const SCROLL_KEY = 'rfq_scroll_y';

    // On load: restore saved position then clear it
    document.addEventListener('DOMContentLoaded', function () {
        const saved = sessionStorage.getItem(SCROLL_KEY);
        if (saved !== null) {
            window.scrollTo(0, parseInt(saved, 10));
            sessionStorage.removeItem(SCROLL_KEY);
        }
    });

    // Before any rfq list navigation: save current position
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.rfq-list-search-form');
        if (form) {
            form.addEventListener('submit', function () {
                sessionStorage.setItem(SCROLL_KEY, window.scrollY);
            });
        }

        document.querySelectorAll('.rfq-sort-link, .btn-secondary, .rfq-page-btn, .rfq-pagination-nav').forEach(function (el) {
            el.addEventListener('click', function () {
                sessionStorage.setItem(SCROLL_KEY, window.scrollY);
            });
        });
    });
}());

// Stage filter for "Total RFQ Value by Stage" table
document.addEventListener('DOMContentLoaded', function () {
    const filterBtns = document.querySelectorAll('.stage-filter-btn');
    const table = document.getElementById('value-by-stage-table');
    if (!table) return;

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const stage = btn.getAttribute('data-stage');

            filterBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = (stage === 'all' || row.getAttribute('data-stage') === stage)
                    ? ''
                    : 'none';
            });
        });
    });
});