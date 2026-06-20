// RFQ / Pipeline module JavaScript

function getAllRFQ(){

};

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