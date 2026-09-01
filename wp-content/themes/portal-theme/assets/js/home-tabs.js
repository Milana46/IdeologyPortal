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

    document.addEventListener('click', function (e) {
        var head = e.target.closest('.portal-accordion__head');
        if (!head) {
            return;
        }

        var root = head.closest('[data-portal-accordion]');
        var item = head.closest('.portal-accordion__item');
        if (!root || !item) {
            return;
        }

        var body = item.querySelector('.portal-accordion__body');
        var mark = head.querySelector('.portal-accordion__mark');
        var willOpen = head.getAttribute('aria-expanded') !== 'true';

        root.querySelectorAll(':scope > .portal-accordion__item').forEach(function (el) {
            var btn = el.querySelector('.portal-accordion__head');
            var panel = el.querySelector('.portal-accordion__body');
            var plus = btn ? btn.querySelector('.portal-accordion__mark') : null;
            el.classList.remove('is-open');
            if (btn) {
                btn.setAttribute('aria-expanded', 'false');
            }
            if (panel) {
                panel.hidden = true;
            }
            if (plus) {
                plus.textContent = '+';
            }
        });

        if (willOpen) {
            item.classList.add('is-open');
            head.setAttribute('aria-expanded', 'true');
            if (body) {
                body.hidden = false;
            }
            if (mark) {
                mark.textContent = '−';
            }
        }
    });
})();
