(function () {
    'use strict';

    var grid = document.getElementById('mediabank-grid');
    var search = document.getElementById('mediabank-search');
    var sortSel = document.getElementById('mediabank-sort');
    var tabBtns = document.querySelectorAll('.mediabank-tabs__btn');
    var emptyEl = document.getElementById('mediabank-empty');

    if (!grid) {
        return;
    }

    function getCards() {
        return Array.prototype.slice.call(grid.querySelectorAll('.mediabank-card'));
    }

    function sortCards() {
        var mode = sortSel ? sortSel.value : 'date-desc';
        var cards = getCards();

        if (mode === 'title-asc') {
            cards.sort(function (a, b) {
                return (a.getAttribute('data-title') || '').localeCompare(
                    b.getAttribute('data-title') || '',
                    'ru'
                );
            });
        } else {
            cards.sort(function (a, b) {
                var ta = parseInt(a.getAttribute('data-sort') || '0', 10);
                var tb = parseInt(b.getAttribute('data-sort') || '0', 10);
                return mode === 'date-asc' ? ta - tb : tb - ta;
            });
        }

        cards.forEach(function (c) {
            grid.appendChild(c);
        });
    }

    function applyFilter() {
        var filter = 'all';
        tabBtns.forEach(function (btn) {
            if (btn.classList.contains('is-active')) {
                filter = btn.getAttribute('data-filter') || 'all';
            }
        });

        var q = search && search.value ? search.value.trim().toLowerCase() : '';
        var visible = 0;

        getCards().forEach(function (card) {
            var type = card.getAttribute('data-type') || '';
            var title = (card.getAttribute('data-title') || '').toLowerCase();
            var matchType = filter === 'all' || type === filter;
            var matchQ = !q || title.indexOf(q) !== -1;
            var show = matchType && matchQ;
            card.hidden = !show;
            if (show) {
                visible++;
            }
        });

        if (emptyEl) {
            emptyEl.hidden = visible > 0;
        }
    }

    function refresh() {
        sortCards();
        applyFilter();
        
        setTimeout(function () {
            try {
                window.dispatchEvent(new Event('resize'));
            } catch (e) {}
        }, 0);
    }

    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tabBtns.forEach(function (b) {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-selected', 'true');
            refresh();
        });
    });

    if (search) {
        search.addEventListener('input', function () {
            sortCards();
            applyFilter();
        });
    }

    if (sortSel) {
        sortSel.addEventListener('change', refresh);
    }

    refresh();

    function initMediabankCarousels() {
        var roots = document.querySelectorAll('[data-mb-carousel]');
        roots.forEach(function (root) {
            if (root.getAttribute('data-mb-init') === '1') {
                return;
            }
            var track = root.querySelector('.mediabank-carousel__track');
            var viewport = root.querySelector('.mediabank-carousel__viewport');
            if (!track || !viewport) {
                return;
            }
            var slides = track.querySelectorAll('.mediabank-carousel__slide');
            var n = slides.length;
            if (n < 2) {
                return;
            }
            root.setAttribute('data-mb-init', '1');
            var idx = 0;
            var prevBtn = root.querySelector('.mediabank-carousel__prev');
            var nextBtn = root.querySelector('.mediabank-carousel__next');
            var dots = root.querySelectorAll('.mediabank-carousel__dot');
            var curEl = root.querySelector('.mediabank-carousel__counter-current');

            function slideWidth() {
                return viewport.offsetWidth || 0;
            }

            function layout() {
                var w = slideWidth();
                if (!w) {
                    return;
                }
                track.style.width = w * n + 'px';
                slides.forEach(function (sl) {
                    sl.style.width = w + 'px';
                });
                go(idx, false);
            }

            function pauseVideos() {
                root.querySelectorAll('.mediabank-carousel__video').forEach(function (v) {
                    try {
                        v.pause();
                    } catch (e) {}
                });
            }

            function go(i, animate) {
                idx = (i + n * 100) % n;
                var w = slideWidth();
                if (!w) {
                    return;
                }
                if (animate === false) {
                    track.style.transition = 'none';
                } else {
                    track.style.transition = '';
                }
                track.style.transform = 'translateX(-' + idx * w + 'px)';
                if (animate === false) {
                    
                    track.offsetHeight;
                    track.style.transition = '';
                }
                dots.forEach(function (d, di) {
                    var on = di === idx;
                    d.classList.toggle('is-active', on);
                    d.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                if (curEl) {
                    curEl.textContent = String(idx + 1);
                }
                pauseVideos();
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    go(idx - 1, true);
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    go(idx + 1, true);
                });
            }
            dots.forEach(function (d) {
                d.addEventListener('click', function (e) {
                    e.preventDefault();
                    var t = parseInt(d.getAttribute('data-slide-to') || '0', 10);
                    if (!isNaN(t)) {
                        go(t, true);
                    }
                });
            });
            root.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    go(idx - 1, true);
                }
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    go(idx + 1, true);
                }
            });

            var touchStartX = null;
            viewport.addEventListener(
                'touchstart',
                function (e) {
                    touchStartX = e.changedTouches[0].screenX;
                },
                { passive: true }
            );
            viewport.addEventListener(
                'touchend',
                function (e) {
                    if (touchStartX === null) {
                        return;
                    }
                    var dx = e.changedTouches[0].screenX - touchStartX;
                    touchStartX = null;
                    if (dx > 50) {
                        go(idx - 1, true);
                    } else if (dx < -50) {
                        go(idx + 1, true);
                    }
                },
                { passive: true }
            );

            var resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(layout, 120);
            });

            layout();
        });
    }

    initMediabankCarousels();
})();
