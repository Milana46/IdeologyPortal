(function () {
    var tablist = document.querySelector('.portal-tabs');

    if (!tablist) {
        return;
    }

    var tabs = tablist.querySelectorAll('.portal-tabs__tab');
    var panels = document.querySelectorAll('.portal-tab-panel');

    function activate(slug) {
        tabs.forEach(function (tab) {
            var on = tab.getAttribute('data-tab') === slug;
            tab.classList.toggle('is-active', on);
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            var on = panel.getAttribute('data-tab-panel') === slug;
            panel.classList.toggle('is-active', on);
            panel.hidden = !on;
        });
    }

    tabs.forEach(function (tab) {
        var slug = tab.getAttribute('data-tab');

        tab.addEventListener('mouseenter', function () {
            activate(slug);
        });

        tab.addEventListener('click', function () {
            activate(slug);
            tab.focus();
        });
    });
})();

(function () {
    var filters = document.querySelector('.portal-iv-filters');

    if (!filters) {
        return;
    }

    var buttons = filters.querySelectorAll('.portal-iv-filter');
    var cards = document.querySelectorAll('.portal-iv-card');
    var empty = document.querySelector('.portal-iv-empty');
    var current = 'all';

    function apply() {
        var visible = 0;
        var showAll = current === '' || current === 'all';

        cards.forEach(function (card) {
            var slug = card.getAttribute('data-workplace') || '';
            var show = showAll || slug === current;
            card.classList.toggle('portal-iv-card--filtered-out', !show);
            if (show) {
                visible += 1;
            }
        });

        buttons.forEach(function (btn) {
            var on = btn.getAttribute('data-workplace') === current;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        if (empty) {
            empty.hidden = visible > 0;
        }
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            current = btn.getAttribute('data-workplace') || 'all';
            apply();
        });
    });
})();
