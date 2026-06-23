console.log('Typhon Cath CRM loaded.');

// Auto-dismiss flash banner after 4 seconds
(function () {
    var banner = document.getElementById('flash-banner');
    if (!banner) return;
    setTimeout(function () {
        banner.classList.add('flash-banner--hiding');
        setTimeout(function () { banner.remove(); }, 400);
    }, 4000);
}());

// Collapsible sidebar
(function () {
    var sidebar  = document.getElementById('app-sidebar');
    var closeBtn = document.getElementById('sidebar-toggle');
    var openBtn  = document.getElementById('sidebar-open-btn');
    if (!sidebar || !closeBtn) return;

    var STORAGE_KEY = 'tc_sidebar_collapsed';

    function setCollapsed(collapsed) {
        sidebar.classList.toggle('collapsed', collapsed);
        if (openBtn) openBtn.style.display = collapsed ? 'block' : 'none';
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    }

    setCollapsed(localStorage.getItem(STORAGE_KEY) === '1');

    closeBtn.addEventListener('click', function () {
        setCollapsed(!sidebar.classList.contains('collapsed'));
    });

    if (openBtn) {
        openBtn.addEventListener('click', function () { setCollapsed(false); });
    }
}());
