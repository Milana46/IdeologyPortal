(function () {
	'use strict';

	var cfg = typeof window.portalKse === 'object' && window.portalKse !== null ? window.portalKse : { events: [], strings: {} };
	var flatEvents = Array.isArray(cfg.events) ? cfg.events.slice() : [];
	var strings = cfg.strings || {};

	var MONTH_NAMES = [
		'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
		'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
	];

	var elGrid = document.getElementById('kse-cal-grid');
	var elHeading = document.getElementById('kse-cal-heading');
	var elMonthSelect = document.getElementById('kse-month-select');
	var elYearSelect = document.getElementById('kse-year-select');
	var elPrev = document.getElementById('kse-prev-month');
	var elNext = document.getElementById('kse-next-month');
	var elSearch = document.getElementById('kse-search');
	var elSort = document.getElementById('kse-sort');
	var elUpcoming = document.getElementById('kse-upcoming-list');
	var elUpcomingEmpty = document.getElementById('kse-upcoming-empty');

	var elModal = document.getElementById('kse-event-modal');
	var elBackdrop = document.getElementById('kse-event-backdrop');
	var elModalDate = document.getElementById('kse-event-modal-date');
	var elModalBody = document.getElementById('kse-event-modal-body');
	var elClose = document.getElementById('kse-event-close');

	var viewYear;
	var viewMonth;
	var selectedISODate = null;
	var initialParams = null;
	try {
		initialParams = new URLSearchParams(window.location.search);
	} catch (e) {}

	
	var displayTypeFilter = 'all';

	if (elUpcomingEmpty && strings.upcomingEmpty) {
		elUpcomingEmpty.textContent = strings.upcomingEmpty;
	}

	function pad(n) {
		return n < 10 ? '0' + n : String(n);
	}

	function toISODate(y, m, d) {
		return y + '-' + pad(m) + '-' + pad(d);
	}

	function parseISODate(s) {
		var p = s.split('-');
		return new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]));
	}

	function mondayWeekday(d) {
		var wd = d.getDay();
		return wd === 0 ? 6 : wd - 1;
	}

	function hasVisibleContent(ev) {
		if (!ev) {
			return false;
		}
		var t = ev.title ? String(ev.title).trim() : '';
		var d = ev.description ? String(ev.description).trim() : '';
		return !!(t || d);
	}

	function eventColor(ev) {
		return ev && ev.color === 'blue' ? 'blue' : 'green';
	}

	function passesTypeFilter(ev) {
		var c = eventColor(ev);
		if (displayTypeFilter === 'all') {
			return true;
		}
		return displayTypeFilter === c;
	}

	function eventsForDate(iso) {
		var out = [];
		var i;
		for (i = 0; i < flatEvents.length; i++) {
			var ev = flatEvents[i];
			if (ev && ev.date === iso && passesTypeFilter(ev) && hasVisibleContent(ev)) {
				out.push(ev);
			}
		}
		out.sort(function (a, b) {
			var ida = Number(a.id) || 0;
			var idb = Number(b.id) || 0;
			return ida - idb;
		});
		return out;
	}

	
	function eventsForModal(iso) {
		var q = searchQuery();
		return eventsForDate(iso).filter(function (ev) {
			return !q || eventMatchesSearch(ev, q);
		});
	}

	function getWeekBounds() {
		var now = new Date();
		now.setHours(0, 0, 0, 0);
		var dow = now.getDay();
		var toMonday = dow === 0 ? -6 : 1 - dow;
		var monday = new Date(now);
		monday.setDate(now.getDate() + toMonday);
		var sunday = new Date(monday);
		sunday.setDate(monday.getDate() + 6);
		return { start: monday, end: sunday };
	}

	function isDateInCurrentWeek(iso) {
		var d = parseISODate(iso);
		d.setHours(0, 0, 0, 0);
		var b = getWeekBounds();
		return d >= b.start && d <= b.end;
	}

	function sortWeekEvents(items, sortMode) {
		var mode = sortMode === 'title' ? 'title' : 'date';
		items.sort(function (a, b) {
			if (mode === 'title') {
				var ta = (a.ev.title || '').toLowerCase();
				var tb = (b.ev.title || '').toLowerCase();
				var byTitle = ta.localeCompare(tb, 'ru');
				if (byTitle !== 0) {
					return byTitle;
				}
			}
			var cmp = a.iso.localeCompare(b.iso);
			if (cmp !== 0) {
				return cmp;
			}
			var ida = Number(a.ev.id) || 0;
			var idb = Number(b.ev.id) || 0;
			return ida - idb;
		});
	}

	function formatHumanDate(iso) {
		var d = parseISODate(iso);
		return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear();
	}

	function searchQuery() {
		return elSearch ? elSearch.value.trim().toLowerCase() : '';
	}

	function eventMatchesSearch(ev, q) {
		if (!q || !ev) {
			return true;
		}
		var t = (ev.title || '').toLowerCase();
		var d = (ev.description || '').toLowerCase();
		return t.indexOf(q) !== -1 || d.indexOf(q) !== -1;
	}

	function typeLabel(ev) {
		return eventColor(ev) === 'blue' ? (strings.typeDocs || 'Подача документов') : (strings.typeVideo || 'Видеоконференция');
	}

	function conferenceUrl(ev) {
		if (!ev || !ev.conferenceUrl) {
			return '';
		}
		var url = String(ev.conferenceUrl).trim();
		if (!url) {
			return '';
		}
		return url;
	}

	function setView(y, m) {
		viewYear = y;
		viewMonth = m;
		if (elMonthSelect) {
			elMonthSelect.value = String(m);
		}
		if (elYearSelect) {
			elYearSelect.value = String(y);
		}
	}

	function syncHeading() {
		if (elHeading) {
			elHeading.textContent = MONTH_NAMES[viewMonth - 1] + ' ' + viewYear;
		}
	}

	function isWeekend(y, m, day) {
		var wd = new Date(y, m - 1, day).getDay();
		return wd === 0 || wd === 6;
	}

	function previewLine(ev) {
		var title = ev.title ? String(ev.title).trim() : '';
		if (title) {
			return title.length > 52 ? title.slice(0, 52) + '…' : title;
		}
		var desc = ev.description ? String(ev.description).replace(/\s+/g, ' ').trim() : '';
		return desc.length > 52 ? desc.slice(0, 52) + '…' : desc;
	}

	function makeCell(dayNum, iso, isCurrentMonth, y, m) {
		var list = eventsForDate(iso);
		var q = searchQuery();
		var anySearchMatch = !q || list.some(function (ev) {
			return eventMatchesSearch(ev, q);
		});
		var dimmed = !!(q && list.length && !anySearchMatch);

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'kse-cal__cell kse-cal__cell--btn';
		btn.setAttribute('role', 'gridcell');
		btn.setAttribute('aria-label', formatHumanDate(iso));

		if (dimmed) {
			btn.classList.add('kse-cal__cell--dimmed');
		}

		if (!isCurrentMonth) {
			btn.classList.add('kse-cal__cell--muted');
		}

		if (isWeekend(y, m, dayNum)) {
			btn.classList.add('kse-cal__cell--weekend');
		}

		var hasGreen = false;
		var hasBlue = false;
		var k;
		for (k = 0; k < list.length; k++) {
			if (eventColor(list[k]) === 'blue') {
				hasBlue = true;
			} else {
				hasGreen = true;
			}
		}

		if (list.length) {
			if (hasGreen) {
				btn.classList.add('kse-cal__cell--mark-green');
			}
			if (hasBlue) {
				btn.classList.add('kse-cal__cell--mark-blue');
			}
		}

		var top = document.createElement('span');
		top.className = 'kse-cal__cell-top';

		var num = document.createElement('span');
		num.className = 'kse-cal__num';
		num.textContent = String(dayNum);
		top.appendChild(num);

		if (list.length) {
			var dotsWrap = document.createElement('span');
			dotsWrap.className = 'kse-cal__dots';
			if (hasGreen && hasBlue) {
				dotsWrap.classList.add('kse-cal__dots--dual');
			}
			if (hasGreen) {
				var dg = document.createElement('span');
				dg.className = 'kse-cal__dot kse-cal__dot--green';
				dg.setAttribute('aria-hidden', 'true');
				dotsWrap.appendChild(dg);
			}
			if (hasBlue) {
				var db = document.createElement('span');
				db.className = 'kse-cal__dot kse-cal__dot--blue';
				db.setAttribute('aria-hidden', 'true');
				dotsWrap.appendChild(db);
			}
			top.appendChild(dotsWrap);
		}

		btn.appendChild(top);

		var previewSource = list[0];
		if (q && list.length) {
			var j;
			for (j = 0; j < list.length; j++) {
				if (eventMatchesSearch(list[j], q)) {
					previewSource = list[j];
					break;
				}
			}
		}
		var previewText = previewSource ? previewLine(previewSource) : '';
		if (list.length > 1) {
			previewText = previewText + ' (+' + (list.length - 1) + ')';
		}
		if (previewText) {
			var prev = document.createElement('span');
			prev.className = 'kse-cal__preview';
			prev.textContent = previewText;
			btn.appendChild(prev);
		}

		btn.addEventListener('click', function () {
			openModal(iso);
		});

		return btn;
	}

	function renderCalendar() {
		if (!elGrid) {
			return;
		}

		syncHeading();

		var first = new Date(viewYear, viewMonth - 1, 1);
		var startPad = mondayWeekday(first);
		var daysInMonth = new Date(viewYear, viewMonth, 0).getDate();
		var prevMonthDays = new Date(viewYear, viewMonth - 1, 0).getDate();

		var cells = [];
		var i;
		var dayNum;
		var yy;
		var mo;
		var iso;

		for (i = 0; i < startPad; i++) {
			dayNum = prevMonthDays - startPad + i + 1;
			yy = viewMonth === 1 ? viewYear - 1 : viewYear;
			mo = viewMonth === 1 ? 12 : viewMonth - 1;
			iso = toISODate(yy, mo, dayNum);
			cells.push(makeCell(dayNum, iso, false, yy, mo));
		}

		for (i = 1; i <= daysInMonth; i++) {
			iso = toISODate(viewYear, viewMonth, i);
			cells.push(makeCell(i, iso, true, viewYear, viewMonth));
		}

		var totalCells = startPad + daysInMonth;
		var endPad = (7 - (totalCells % 7)) % 7;
		for (i = 1; i <= endPad; i++) {
			yy = viewMonth === 12 ? viewYear + 1 : viewYear;
			mo = viewMonth === 12 ? 1 : viewMonth + 1;
			iso = toISODate(yy, mo, i);
			cells.push(makeCell(i, iso, false, yy, mo));
		}

		elGrid.innerHTML = '';
		var r;
		var c;
		var row;
		for (r = 0; r < cells.length / 7; r++) {
			row = document.createElement('div');
			row.className = 'kse-cal__row';
			row.setAttribute('role', 'row');
			for (c = 0; c < 7; c++) {
				row.appendChild(cells[r * 7 + c]);
			}
			elGrid.appendChild(row);
		}

		renderUpcoming();
	}

	function renderUpcoming() {
		if (!elUpcoming) {
			return;
		}

		var q = searchQuery();
		var sortMode = elSort && elSort.value ? elSort.value : 'date';

		var items = [];
		var n;
		for (n = 0; n < flatEvents.length; n++) {
			var ev = flatEvents[n];
			if (!hasVisibleContent(ev) || !passesTypeFilter(ev)) {
				continue;
			}
			if (!ev.date || !isDateInCurrentWeek(ev.date)) {
				continue;
			}
			if (!eventMatchesSearch(ev, q)) {
				continue;
			}
			items.push({ iso: ev.date, ev: ev });
		}

		sortWeekEvents(items, sortMode);

		elUpcoming.innerHTML = '';

		if (items.length === 0) {
			if (elUpcomingEmpty) {
				elUpcomingEmpty.hidden = false;
			}
			return;
		}

		if (elUpcomingEmpty) {
			elUpcomingEmpty.hidden = true;
		}

		items.forEach(function (item) {
			var ev = item.ev;
			var color = eventColor(ev);
			var hd = parseISODate(item.iso);
			var dateLabel = pad(hd.getDate()) + '.' + pad(hd.getMonth() + 1) + '.' + hd.getFullYear();

			var li = document.createElement('li');
			li.className = 'kse-upcoming-item';

			var dot = document.createElement('span');
			dot.className = 'kse-upcoming-item__dot kse-upcoming-item__dot--' + color;
			dot.setAttribute('aria-hidden', 'true');

			var body = document.createElement('div');
			body.className = 'kse-upcoming-item__body';

			var name = document.createElement('strong');
			name.className = 'kse-upcoming-item__title';
			name.textContent = ev.title || '—';

			var dateEl = document.createElement('p');
			dateEl.className = 'kse-upcoming-item__date';
			dateEl.textContent = dateLabel;

			body.appendChild(name);
			if (ev.description) {
				var info = document.createElement('p');
				info.className = 'kse-upcoming-item__info';
				info.textContent = ev.description;
				body.appendChild(info);
			}
			var confUrlUpcoming = conferenceUrl(ev);
			if (confUrlUpcoming) {
				var confLink = document.createElement('a');
				confLink.className = 'kse-upcoming-item__link';
				confLink.href = confUrlUpcoming;
				confLink.target = '_blank';
				confLink.rel = 'noopener noreferrer';
				confLink.textContent = strings.conferenceLink || 'Перейти к видеоконференции';
				body.appendChild(confLink);
			}
			body.appendChild(dateEl);

			li.appendChild(dot);
			li.appendChild(body);
			elUpcoming.appendChild(li);
		});
	}

	function renderModalBody(iso) {
		if (!elModalBody) {
			return;
		}
		var list = eventsForModal(iso);
		elModalBody.innerHTML = '';

		if (!list.length) {
			var empty = document.createElement('p');
			empty.className = 'kse-modal__empty';
			empty.textContent = strings.noEvents || '';
			elModalBody.appendChild(empty);
			return;
		}

		list.forEach(function (ev) {
			var card = document.createElement('article');
			card.className = 'kse-modal__view-item';

			var head = document.createElement('div');
			head.className = 'kse-modal__view-item-head';

			var badge = document.createElement('span');
			badge.className = 'kse-modal__type-badge kse-modal__type-badge--' + eventColor(ev);
			badge.textContent = typeLabel(ev);

			var titleEl = document.createElement('h4');
			titleEl.className = 'kse-modal__view-item-title';
			titleEl.textContent = ev.title || '—';

			head.appendChild(badge);
			head.appendChild(titleEl);
			card.appendChild(head);

			if (ev.description) {
				var desc = document.createElement('p');
				desc.className = 'kse-modal__view-item-desc';
				desc.textContent = ev.description;
				card.appendChild(desc);
			}
			var confUrl = conferenceUrl(ev);
			if (confUrl) {
				var actionLink = document.createElement('a');
				actionLink.className = 'kse-modal__view-item-link';
				actionLink.href = confUrl;
				actionLink.target = '_blank';
				actionLink.rel = 'noopener noreferrer';
				actionLink.textContent = strings.conferenceLink || 'Перейти к видеоконференции';
				card.appendChild(actionLink);
			}

			elModalBody.appendChild(card);
		});
	}

	function openModal(iso) {
		selectedISODate = iso;
		if (elModalDate) {
			elModalDate.textContent = formatHumanDate(iso);
		}
		renderModalBody(iso);
		if (elModal) {
			elModal.hidden = false;
		}
		if (elClose) {
			elClose.focus();
		}
	}

	function closeModal() {
		if (elModal) {
			elModal.hidden = true;
		}
		selectedISODate = null;
	}

	function initCalendar() {
		if (!elGrid || !elHeading) {
			return;
		}

		var now = new Date();
		setView(now.getFullYear(), now.getMonth() + 1);

		if (initialParams) {
			var initialSearch = initialParams.get('portal_find');
			if (initialSearch && elSearch) {
				elSearch.value = initialSearch;
			}
			var initialDate = initialParams.get('portal_date');
			if (initialDate && /^\d{4}-\d{2}-\d{2}$/.test(initialDate)) {
				var parts = initialDate.split('-');
				var y = parseInt(parts[0], 10);
				var m = parseInt(parts[1], 10);
				if (!isNaN(y) && !isNaN(m) && m >= 1 && m <= 12) {
					setView(y, m);
				}
			}
		}

		var activeTabBtn = document.querySelector('.kse-tabs__btn.is-active');
		if (activeTabBtn) {
			var t0 = activeTabBtn.getAttribute('data-kse-tab');
			if (t0 === 'video') {
				displayTypeFilter = 'green';
			} else if (t0 === 'docs') {
				displayTypeFilter = 'blue';
			} else {
				displayTypeFilter = 'all';
			}
			if (t0 === 'april') {
				setView(viewYear, 4);
			}
		}

		renderCalendar();

		if (elMonthSelect) {
			elMonthSelect.addEventListener('change', function () {
				setView(viewYear, parseInt(elMonthSelect.value, 10));
				renderCalendar();
			});
		}

		if (elYearSelect) {
			elYearSelect.addEventListener('change', function () {
				setView(parseInt(elYearSelect.value, 10), viewMonth);
				renderCalendar();
			});
		}

		if (elPrev) {
			elPrev.addEventListener('click', function () {
				if (viewMonth === 1) {
					setView(viewYear - 1, 12);
				} else {
					setView(viewYear, viewMonth - 1);
				}
				renderCalendar();
			});
		}

		if (elNext) {
			elNext.addEventListener('click', function () {
				if (viewMonth === 12) {
					setView(viewYear + 1, 1);
				} else {
					setView(viewYear, viewMonth + 1);
				}
				renderCalendar();
			});
		}

		if (elSearch) {
			elSearch.addEventListener('input', function () {
				renderCalendar();
				if (elModal && !elModal.hidden && selectedISODate) {
					renderModalBody(selectedISODate);
				}
			});
		}

		if (elSort) {
			elSort.addEventListener('change', function () {
				renderUpcoming();
			});
		}

		var tabButtons = document.querySelectorAll('.kse-tabs__btn');
		tabButtons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				tabButtons.forEach(function (b) {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				});
				btn.classList.add('is-active');
				btn.setAttribute('aria-selected', 'true');

				var tab = btn.getAttribute('data-kse-tab');
				if (tab === 'april') {
					setView(viewYear, 4);
					displayTypeFilter = 'all';
				} else if (tab === 'all') {
					displayTypeFilter = 'all';
				} else if (tab === 'video') {
					displayTypeFilter = 'green';
				} else if (tab === 'docs') {
					displayTypeFilter = 'blue';
				}
				renderCalendar();
				if (elModal && !elModal.hidden && selectedISODate) {
					renderModalBody(selectedISODate);
				}
			});
		});

		if (elClose) {
			elClose.addEventListener('click', closeModal);
		}
		if (elBackdrop) {
			elBackdrop.addEventListener('click', closeModal);
		}

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && elModal && !elModal.hidden) {
				closeModal();
			}
		});
	}

	initCalendar();
})();
