// Reusable server-backed autocomplete for form pickers (account / contact / product).
//
// Instead of embedding an entire table in the page and filtering client-side,
// this fetches matches from a JSON endpoint as the user types. Endpoint contract:
//   GET url?q=<term>[&<extra params>]  -> [{ id, label, ... }, ...]   (capped list)
//   GET url?id=<id>                    -> [{ id, label, ... }]         (resolve one label)
//
// Usage:
//   initServerDropdown(searchInputEl, hiddenIdEl, resultsEl, {
//       url: '/modules/rfq/search_accounts.php',
//       params: () => ({ account_id: someHidden.value }),  // optional extra query params
//       onChanged: (id, item) => { ... },                  // optional
//   });
//
// Preserves the same DOM classes as the old inline picker so existing CSS applies.
(function () {
    function initServerDropdown(searchEl, hiddenEl, resultsEl, options) {
        options = options || {};
        const url        = options.url;
        const getParams  = typeof options.params === 'function' ? options.params : function () { return {}; };
        const onChanged  = typeof options.onChanged === 'function' ? options.onChanged : function () {};
        const minChars   = options.minChars || 0;
        const debounceMs = options.debounceMs != null ? options.debounceMs : 200;

        let focusedIdx = -1;
        let reqToken = 0;          // guards against out-of-order responses
        let debounceTimer = null;
        let currentItems = [];

        function buildUrl(extra) {
            const params = new URLSearchParams(getParams() || {});
            Object.keys(extra || {}).forEach(function (k) { params.set(k, extra[k]); });
            return url + (url.indexOf('?') === -1 ? '?' : '&') + params.toString();
        }

        function fetchItems(extra) {
            const token = ++reqToken;
            return fetch(buildUrl(extra), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : []; })
                .then(function (data) {
                    if (token !== reqToken) return null; // a newer request superseded this one
                    return Array.isArray(data) ? data : [];
                })
                .catch(function () { return []; });
        }

        // Resolve the label for a pre-selected id (edit forms / validation repopulation).
        if (hiddenEl.value) {
            fetchItems({ id: hiddenEl.value }).then(function (items) {
                if (!items || !items.length) return;
                const match = items.find(function (i) { return String(i.id) === String(hiddenEl.value); }) || items[0];
                if (match) {
                    searchEl.value = match.label;
                    searchEl.classList.add('rfq-search-input--selected');
                }
            });
        }

        function renderResults(items) {
            currentItems = items || [];
            resultsEl.innerHTML = '';
            focusedIdx = -1;

            if (!currentItems.length) {
                resultsEl.innerHTML = '<div class="rfq-search-option--empty">No results</div>';
                return;
            }
            currentItems.forEach(function (item) {
                const div = document.createElement('div');
                div.className = 'rfq-search-option';
                div.textContent = item.label;
                div.dataset.id = item.id;
                div.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectItem(item);
                });
                resultsEl.appendChild(div);
            });
        }

        function selectItem(item) {
            hiddenEl.value = item.id;
            searchEl.value = item.label;
            searchEl.classList.add('rfq-search-input--selected');
            resultsEl.style.display = 'none';
            focusedIdx = -1;
            onChanged(String(item.id), item);
        }

        function clearSelection() {
            if (hiddenEl.value !== '') {
                hiddenEl.value = '';
                onChanged('', null);
            }
            searchEl.classList.remove('rfq-search-input--selected');
        }

        function query(term) {
            if (term.length < minChars) { renderResults([]); return; }
            fetchItems({ q: term }).then(function (items) {
                if (items === null) return;
                renderResults(items);
                resultsEl.style.display = '';
            });
        }

        searchEl.addEventListener('input', function () {
            clearSelection();
            resultsEl.style.display = '';
            const term = this.value;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { query(term); }, debounceMs);
        });

        searchEl.addEventListener('focus', function () {
            query(this.value);
        });

        searchEl.addEventListener('blur', function () {
            setTimeout(function () { resultsEl.style.display = 'none'; }, 150);
            if (!hiddenEl.value && this.value) this.value = '';
        });

        searchEl.addEventListener('keydown', function (e) {
            const opts = resultsEl.querySelectorAll('.rfq-search-option');
            if (!opts.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                focusedIdx = Math.min(focusedIdx + 1, opts.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusedIdx = Math.max(focusedIdx - 1, 0);
            } else if (e.key === 'Enter' && focusedIdx >= 0) {
                e.preventDefault();
                const match = currentItems.find(function (i) { return String(i.id) === opts[focusedIdx].dataset.id; });
                if (match) selectItem(match);
                return;
            } else if (e.key === 'Escape') {
                resultsEl.style.display = 'none';
                return;
            } else {
                return;
            }

            opts.forEach(function (o, i) { o.classList.toggle('rfq-search-option--focused', i === focusedIdx); });
            if (opts[focusedIdx]) opts[focusedIdx].scrollIntoView({ block: 'nearest' });
        });
    }

    window.initServerDropdown = initServerDropdown;
}());
