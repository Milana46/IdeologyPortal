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
