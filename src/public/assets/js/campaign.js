// Campaign module JavaScript

// ── Top Performers: type filter ───────────────────────────────────────────────
(function () {
    var btns = document.querySelectorAll('.camp-type-btn');
    if (!btns.length) return;

    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var type = btn.getAttribute('data-type');
            btns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var rank = 1;
            document.querySelectorAll('#top-performers-table tbody tr').forEach(function (row) {
                var show = type === 'all' || row.getAttribute('data-type') === type;
                row.style.display = show ? '' : 'none';
                if (show) row.cells[0].textContent = rank++;
            });
        });
    });
}());

// ── Campaign Momentum Chart ───────────────────────────────────────────────────
(function () {
    var canvas = document.getElementById('momentum-chart');
    if (!canvas || typeof Chart === 'undefined' || !window.__campMomentum) return;

    var d = window.__campMomentum;

    var barDefs = {
        recipients: {
            label: 'Recipients Reached',
            data: d.recipients,
            backgroundColor: 'rgba(26,86,219,0.75)',
            borderColor: '#1a56db',
            borderWidth: 1,
            borderRadius: 4,
        },
        sent: {
            label: 'Campaigns Sent',
            data: d.sent,
            backgroundColor: 'rgba(25,135,84,0.75)',
            borderColor: '#198754',
            borderWidth: 1,
            borderRadius: 4,
        },
        created: {
            label: 'Campaigns Created',
            data: d.created,
            backgroundColor: 'rgba(109,40,217,0.65)',
            borderColor: '#6d28d9',
            borderWidth: 1,
            borderRadius: 4,
        }
    };

    var activeMetric = 'recipients';

    function makeDatasets(metric) {
        return [
            Object.assign({ type: 'bar', yAxisID: 'y', order: 2 }, barDefs[metric]),
            {
                type: 'line',
                label: 'Avg Open Rate %',
                data: d.openRate,
                yAxisID: 'y2',
                order: 1,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.10)',
                pointBackgroundColor: '#f59e0b',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.35,
                fill: true,
                borderWidth: 2,
                spanGaps: true,
            },
            {
                type: 'line',
                label: 'Avg Click Rate %',
                data: d.clickRate,
                yAxisID: 'y2',
                order: 1,
                borderColor: '#1a56db',
                backgroundColor: 'rgba(26,86,219,0.08)',
                pointBackgroundColor: '#1a56db',
                pointRadius: 4,
                tension: 0.35,
                fill: true,
                borderWidth: 2,
                spanGaps: true,
            }
        ];
    }

    var chart = new Chart(canvas, {
        data: {
            labels: d.labels,
            datasets: makeDatasets(activeMetric)
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 12 }, padding: 18, usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            var v = ctx.parsed.y;
                            if (ctx.dataset.yAxisID === 'y2')
                                return ' ' + ctx.dataset.label + ': ' + (v !== null ? v.toFixed(1) + '%' : '—');
                            return ' ' + ctx.dataset.label + ': ' + (v !== null ? v.toLocaleString() : '—');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: { display: true, text: 'Volume', font: { size: 11 }, color: '#6b7280' },
                    ticks: { precision: 0, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.06)' }
                },
                y2: {
                    beginAtZero: true,
                    position: 'right',
                    min: 0,
                    max: 100,
                    title: { display: true, text: 'Rate %', font: { size: 11 }, color: '#6b7280' },
                    ticks: { callback: function (v) { return v + '%'; }, font: { size: 11 } },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // Metric toggle
    document.querySelectorAll('.camp-metric-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeMetric = btn.getAttribute('data-metric');
            document.querySelectorAll('.camp-metric-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            chart.data.datasets = makeDatasets(activeMetric);
            chart.update();
        });
    });
}());
