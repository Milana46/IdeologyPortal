(function () {
	'use strict';

	var cfg = typeof window.portalCalendarMerop === 'object' && window.portalCalendarMerop !== null
		? window.portalCalendarMerop
		: { events: [], strings: {} };
	var flatEvents = Array.isArray(cfg.events) ? cfg.events.slice() : [];
	var strings = cfg.strings || {};

	var MONTH_NAMES = [
		'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
		'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
	];

	
	var FIXED_STATE_MD = {
		'01-01': true,
		'01-07': true,
		'03-08': true,
		'05-01': true,
		'05-09': true,
		'05-10': true,
		'07-03': true,
		'11-07': true,
		'12-25': true
	};

	var STATE_TITLE_BY_MD = {
		'01-01': 'Новый год',
		'01-07': 'Рождество Христово (православное)',
		'03-08': 'Международный женский день',
		'05-01': 'Праздник Труда',
		'05-09': 'День Победы',
		'05-10': 'День Государственного флага и Государственного герба',
		'07-03': 'День Независимости Республики Беларусь',
		'11-07': 'День Октябрьской революции',
		'12-25': 'Рождество Христово (католическое)'
	};

	var RECUR_YEARS_AHEAD = 15;

	var viewYear;
	var viewMonth;
	var selectedISODate = null;
	var initialParams = null;
	try {
		initialParams = new URLSearchParams(window.location.search);
	} catch (e) {}

	var elGrid = document.getElementById('calendar-grid');
	var elMonth = document.getElementById('calendar-month-heading');
	var elPrev = document.getElementById('calendar-prev-month');
	var elNext = document.getElementById('calendar-next-month');
	var elModal = document.getElementById('calendar-note-modal');
	var elBackdrop = document.getElementById('calendar-note-backdrop');
	var elModalDate = document.getElementById('calendar-note-modal-date');
	var elModalBody = document.getElementById('calendar-note-modal-body');
	var elModalTitle = document.getElementById('calendar-note-modal-title');
	var elClose = document.getElementById('calendar-note-close');
	var elCloseX = document.getElementById('calendar-note-close-x');
	var elSearch = document.getElementById('calendar-search-input');
	var elSort = document.getElementById('calendar-sort-select');
	var elHolidayList = document.getElementById('calendar-holidays-list');
	var elMeropList = document.getElementById('calendar-merop-list');
	var elFoundationList = document.getElementById('calendar-foundation-list');

	if (elModalTitle && strings.modalTitle) {
		elModalTitle.textContent = strings.modalTitle;
	}

	if (!elGrid || !elMonth) {
		return;
	}

	function pad(n) {
		return n < 10 ? '0' + n : String(n);
	}

	function isoToMD(iso) {
		var p = iso.split('-');
		if (p.length < 3) {
			return '';
		}
		return pad(Number(p[1], 10)) + '-' + pad(Number(p[2], 10));
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

	function eventType(ev) {
		var t = ev && ev.type ? String(ev.type) : 'merop';
		if (t === 'holiday' || t === 'foundation' || t === 'merop') {
			return t;
		}
		return 'merop';
	}

	function isRecurringType(type) {
		return type === 'holiday' || type === 'foundation';
	}

	function mdExistsInYear(year, md) {
		var p = md.split('-');
		if (p.length < 2) {
			return false;
		}
		var m = Number(p[0], 10);
		var d = Number(p[1], 10);
		if (!m || !d) {
			return false;
		}
		var dt = new Date(year, m - 1, d);
		return dt.getFullYear() === year && dt.getMonth() === m - 1 && dt.getDate() === d;
	}

	function projectEventToIso(ev, iso) {
		return {
			id: ev.id,
			date: iso,
			title: ev.title,
			description: ev.description,
			type: eventType(ev),
			sourceDate: ev.date
		};
	}

	function eventsOfTypeOnDate(iso, type) {
		var out = [];
		var i;
		var targetMd = isoToMD(iso);
		var recurring = isRecurringType(type);

		for (i = 0; i < flatEvents.length; i++) {
			var ev = flatEvents[i];
			if (!ev || !hasVisibleContent(ev) || eventType(ev) !== type) {
				continue;
			}
			if (recurring) {
				if (ev.date && isoToMD(ev.date) === targetMd) {
					out.push(projectEventToIso(ev, iso));
				}
			} else if (ev.date === iso) {
				out.push(ev);
			}
		}
		out.sort(function (a, b) {
			return (Number(a.id) || 0) - (Number(b.id) || 0);
		});
		return out;
	}

	function buildFixedHolidays(fromYear, toYear) {
		var items = [];
		var y;
		var md;
		for (y = fromYear; y <= toYear; y++) {
			for (md in STATE_TITLE_BY_MD) {
				if (!Object.prototype.hasOwnProperty.call(STATE_TITLE_BY_MD, md)) {
					continue;
				}
				if (!mdExistsInYear(y, md)) {
					continue;
				}
				items.push({
					date: y + '-' + md,
					title: STATE_TITLE_BY_MD[md]
				});
			}
		}
		return items;
	}

	function isStateHolidayIso(iso) {
		var md = isoToMD(iso);
		if (FIXED_STATE_MD[md]) {
			return true;
		}
		return eventsOfTypeOnDate(iso, 'holiday').length > 0;
	}

	function stateHolidayTitlesForDate(iso) {
		var titles = [];
		var seen = {};
		var md = isoToMD(iso);
		var t;

		if (FIXED_STATE_MD[md] && STATE_TITLE_BY_MD[md]) {
			titles.push(STATE_TITLE_BY_MD[md]);
			seen[STATE_TITLE_BY_MD[md]] = true;
		}

		var adminHolidays = eventsOfTypeOnDate(iso, 'holiday');
		var i;
		for (i = 0; i < adminHolidays.length; i++) {
			t = adminHolidays[i].title || '';
			if (t && !seen[t]) {
				titles.push(t);
				seen[t] = true;
			}
		}
		return titles;
	}

	function occurrencesFromMd(md, fromDate, yearsAhead) {
		var out = [];
		if (!md || md.length < 5) {
			return out;
		}
		var startY = fromDate.getFullYear();
		var endY = startY + (yearsAhead || RECUR_YEARS_AHEAD);
		var y;
		for (y = startY; y <= endY; y++) {
			if (!mdExistsInYear(y, md)) {
				continue;
			}
			var iso = y + '-' + md;
			var d = parseISODate(iso);
			d.setHours(0, 0, 0, 0);
			if (d >= fromDate) {
				out.push(iso);
			}
		}
		return out;
	}

	function appendMarkDots(btn, marks) {
		if (!marks.holiday && !marks.merop && !marks.foundation) {
			return;
		}
		var wrap = document.createElement('span');
		wrap.className = 'calendar-day__marks';
		if (marks.holiday) {
			var dh = document.createElement('span');
			dh.className = 'calendar-day__dot calendar-day__dot--holiday';
			dh.setAttribute('aria-hidden', 'true');
			wrap.appendChild(dh);
		}
		if (marks.merop) {
			var dm = document.createElement('span');
			dm.className = 'calendar-day__dot calendar-day__dot--merop';
			dm.setAttribute('aria-hidden', 'true');
			wrap.appendChild(dm);
		}
		if (marks.foundation) {
			var df = document.createElement('span');
			df.className = 'calendar-day__dot calendar-day__dot--foundation';
			df.setAttribute('aria-hidden', 'true');
			wrap.appendChild(df);
		}
		btn.appendChild(wrap);
	}

	function setView(y, m) {
		viewYear = y;
		viewMonth = m;
	}

	function renderMonth() {
		elMonth.textContent = MONTH_NAMES[viewMonth - 1] + ' ' + viewYear;

		var first = new Date(viewYear, viewMonth - 1, 1);
		var startPad = mondayWeekday(first);
		var daysInMonth = new Date(viewYear, viewMonth, 0).getDate();
		var prevMonthDays = new Date(viewYear, viewMonth - 1, 0).getDate();

		elGrid.innerHTML = '';

		var i;
		var cell;
		var dayNum;
		var y;
		var mo;
		var iso;

		for (i = 0; i < startPad; i++) {
			dayNum = prevMonthDays - startPad + i + 1;
			y = viewMonth === 1 ? viewYear - 1 : viewYear;
			mo = viewMonth === 1 ? 12 : viewMonth - 1;
			iso = toISODate(y, mo, dayNum);
			cell = makeDayCell(dayNum, iso, false, y, mo);
			elGrid.appendChild(cell);
		}

		for (i = 1; i <= daysInMonth; i++) {
			iso = toISODate(viewYear, viewMonth, i);
			cell = makeDayCell(i, iso, true, viewYear, viewMonth);
			elGrid.appendChild(cell);
		}

		var totalCells = startPad + daysInMonth;
		var endPad = (7 - (totalCells % 7)) % 7;
		for (i = 1; i <= endPad; i++) {
			y = viewMonth === 12 ? viewYear + 1 : viewYear;
			mo = viewMonth === 12 ? 1 : viewMonth + 1;
			iso = toISODate(y, mo, i);
			cell = makeDayCell(i, iso, false, y, mo);
			elGrid.appendChild(cell);
		}
	}

	function makeDayCell(num, iso, isCurrentMonth, y, m) {
		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'calendar-day';
		btn.setAttribute('role', 'gridcell');

		var span = document.createElement('span');
		span.className = 'calendar-day__num';
		span.textContent = String(num);
		btn.appendChild(span);

		var marks = {
			holiday: isStateHolidayIso(iso),
			merop: eventsOfTypeOnDate(iso, 'merop').length > 0,
			foundation: eventsOfTypeOnDate(iso, 'foundation').length > 0
		};

		if (isCurrentMonth && m === 5 && num === 10 && isoToMD(iso) === '05-10') {
			var flagEl = document.createElement('span');
			flagEl.className = 'calendar-day__flag';
			flagEl.setAttribute('aria-hidden', 'true');
			flagEl.textContent = '\uD83C\uDDE7\uD83C\uDDFE';
			btn.appendChild(flagEl);
		}

		appendMarkDots(btn, marks);

		if (!isCurrentMonth) {
			btn.classList.add('calendar-day--outside');
		} else {
			btn.addEventListener('click', function () {
				openModal(iso);
			});
		}

		return btn;
	}

	function formatHumanDate(iso) {
		var d = parseISODate(iso);
		return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear();
	}

	function addModalSection(titleText, bodyEl) {
		var sec = document.createElement('section');
		sec.className = 'calendar-modal__section';
		var h = document.createElement('h4');
		h.className = 'calendar-modal__section-title';
		h.textContent = titleText;
		sec.appendChild(h);
		sec.appendChild(bodyEl);
		elModalBody.appendChild(sec);
	}

	function renderEventArticles(list, emptyText) {
		var wrap = document.createElement('div');
		wrap.className = 'calendar-modal__section-body';
		if (list.length) {
			list.forEach(function (ev) {
				var art = document.createElement('article');
				art.className = 'calendar-modal__view-item';
				var h = document.createElement('h4');
				h.className = 'calendar-modal__view-item-title';
				h.textContent = ev.title || '—';
				art.appendChild(h);
				if (ev.description) {
					var desc = document.createElement('p');
					desc.className = 'calendar-modal__view-item-desc';
					desc.textContent = ev.description;
					art.appendChild(desc);
				}
				wrap.appendChild(art);
			});
		} else {
			var pm = document.createElement('p');
			pm.className = 'calendar-modal__view-empty';
			pm.textContent = emptyText || '';
			wrap.appendChild(pm);
		}
		return wrap;
	}

	function renderModalBody(iso) {
		if (!elModalBody) {
			return;
		}
		elModalBody.innerHTML = '';

		var holidayTitles = stateHolidayTitlesForDate(iso);
		var merop = eventsOfTypeOnDate(iso, 'merop');
		var foundation = eventsOfTypeOnDate(iso, 'foundation');

		var holWrap = document.createElement('div');
		holWrap.className = 'calendar-modal__section-body';
		if (holidayTitles.length) {
			holidayTitles.forEach(function (title) {
				var p = document.createElement('p');
				p.className = 'calendar-modal__holiday-line';
				p.textContent = title;
				holWrap.appendChild(p);
			});
			var adminHolidays = eventsOfTypeOnDate(iso, 'holiday');
			adminHolidays.forEach(function (ev) {
				if (ev.description) {
					var desc = document.createElement('p');
					desc.className = 'calendar-modal__view-item-desc';
					desc.textContent = ev.description;
					holWrap.appendChild(desc);
				}
			});
		} else {
			var pe = document.createElement('p');
			pe.className = 'calendar-modal__view-empty';
			pe.textContent = strings.noHolidayDay || '';
			holWrap.appendChild(pe);
		}
		addModalSection(strings.sectionState || 'Государственные праздники', holWrap);

		addModalSection(
			strings.sectionMerop || 'Мероприятия',
			renderEventArticles(merop, strings.noMeropDay || '')
		);

		addModalSection(
			strings.sectionFoundation || 'День основания предприятия',
			renderEventArticles(foundation, strings.noFoundationDay || '')
		);
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

	if (elClose) {
		elClose.addEventListener('click', closeModal);
	}
	if (elCloseX) {
		elCloseX.addEventListener('click', closeModal);
	}
	if (elBackdrop) {
		elBackdrop.addEventListener('click', closeModal);
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && elModal && !elModal.hidden) {
			closeModal();
		}
	});

	if (elPrev) {
		elPrev.addEventListener('click', function () {
			if (viewMonth === 1) {
				setView(viewYear - 1, 12);
			} else {
				setView(viewYear, viewMonth - 1);
			}
			renderMonth();
		});
	}

	if (elNext) {
		elNext.addEventListener('click', function () {
			if (viewMonth === 12) {
				setView(viewYear + 1, 1);
			} else {
				setView(viewYear, viewMonth + 1);
			}
			renderMonth();
		});
	}

	function holidayMatchesSearch(item, q) {
		if (!q) {
			return true;
		}
		var ql = q.toLowerCase();
		return item.title.toLowerCase().indexOf(ql) !== -1 || item.date.indexOf(ql) !== -1;
	}

	function meropMatchesSearch(ev, q) {
		if (!q) {
			return true;
		}
		var ql = q.toLowerCase();
		return (ev.title || '').toLowerCase().indexOf(ql) !== -1 ||
			(ev.description || '').toLowerCase().indexOf(ql) !== -1 ||
			(ev.date || '').indexOf(ql) !== -1;
	}

	function sortMode() {
		return elSort && elSort.value ? elSort.value : 'date-asc';
	}

	function collectAdminHolidayItems(q, fromDate) {
		var items = [];
		var seen = {};
		var n;
		for (n = 0; n < flatEvents.length; n++) {
			var ev = flatEvents[n];
			if (!hasVisibleContent(ev) || !ev.date || eventType(ev) !== 'holiday') {
				continue;
			}
			if (!meropMatchesSearch(ev, q)) {
				continue;
			}
			var md = isoToMD(ev.date);
			var occ = occurrencesFromMd(md, fromDate, RECUR_YEARS_AHEAD);
			var oi;
			for (oi = 0; oi < occ.length; oi++) {
				var key = occ[oi] + '|' + (ev.title || '');
				if (seen[key]) {
					continue;
				}
				seen[key] = true;
				items.push({ date: occ[oi], title: ev.title || '—' });
			}
		}
		return items;
	}

	function sortByMode(items) {
		var sort = sortMode();
		items.sort(function (a, b) {
			if (sort === 'date-desc') {
				return b.date.localeCompare(a.date);
			}
			if (sort === 'title-asc') {
				return (a.title || '').localeCompare(b.title || '', 'ru');
			}
			return a.date.localeCompare(b.date);
		});
		return items;
	}

	function renderHolidaysList() {
		if (!elHolidayList) {
			return;
		}

		var today = new Date();
		today.setHours(0, 0, 0, 0);
		var q = elSearch ? elSearch.value.trim().toLowerCase() : '';
		var fromY = today.getFullYear();
		var toY = fromY + RECUR_YEARS_AHEAD;

		var items = buildFixedHolidays(fromY, toY).filter(function (h) {
			var hd = parseISODate(h.date);
			hd.setHours(0, 0, 0, 0);
			return hd >= today && holidayMatchesSearch(h, q);
		});

		var adminItems = collectAdminHolidayItems(q, today);
		var seen = {};
		items.forEach(function (h) {
			seen[h.date + '|' + h.title] = true;
		});
		adminItems.forEach(function (h) {
			var key = h.date + '|' + h.title;
			if (!seen[key]) {
				items.push(h);
				seen[key] = true;
			}
		});

		sortByMode(items);

		elHolidayList.innerHTML = '';
		items.slice(0, 16).forEach(function (h) {
			var li = document.createElement('li');
			li.className = 'calendar-holidays__row calendar-holidays__row--state';
			var hd = parseISODate(h.date);
			var label = pad(hd.getDate()) + ' ' + MONTH_NAMES[hd.getMonth()].toLowerCase();

			var dot = document.createElement('span');
			dot.className = 'calendar-holidays__mark calendar-holidays__mark--holiday';
			dot.setAttribute('aria-hidden', 'true');

			var text = document.createElement('div');
			text.className = 'calendar-holidays__row-text';
			var strong = document.createElement('strong');
			strong.textContent = label;
			text.appendChild(strong);
			text.appendChild(document.createTextNode(' — ' + h.title));

			li.appendChild(dot);
			li.appendChild(text);
			elHolidayList.appendChild(li);
		});

		if (items.length === 0) {
			var empty = document.createElement('li');
			empty.className = 'calendar-holidays__empty';
			empty.textContent = strings.emptyHolidays || 'Нет записей по запросу.';
			elHolidayList.appendChild(empty);
		}
	}

	function renderTypedEventList(elList, type, markClass, rowClass, emptyKey, emptyFallback) {
		if (!elList) {
			return;
		}

		var today = new Date();
		today.setHours(0, 0, 0, 0);
		var q = elSearch ? elSearch.value.trim().toLowerCase() : '';
		var recurring = isRecurringType(type);

		var items = [];
		var n;
		for (n = 0; n < flatEvents.length; n++) {
			var ev = flatEvents[n];
			if (!hasVisibleContent(ev) || !ev.date || eventType(ev) !== type) {
				continue;
			}
			if (!meropMatchesSearch(ev, q)) {
				continue;
			}

			if (recurring) {
				var occ = occurrencesFromMd(isoToMD(ev.date), today, RECUR_YEARS_AHEAD);
				var oi;
				for (oi = 0; oi < occ.length; oi++) {
					items.push(projectEventToIso(ev, occ[oi]));
				}
			} else {
				var hd = parseISODate(ev.date);
				hd.setHours(0, 0, 0, 0);
				if (hd < today) {
					continue;
				}
				items.push(ev);
			}
		}

		var sort = sortMode();
		items.sort(function (a, b) {
			if (sort === 'date-desc') {
				var c = b.date.localeCompare(a.date);
				if (c !== 0) {
					return c;
				}
			} else if (sort === 'title-asc') {
				var bt = (a.title || '').localeCompare(b.title || '', 'ru');
				if (bt !== 0) {
					return bt;
				}
			} else {
				var ca = a.date.localeCompare(b.date);
				if (ca !== 0) {
					return ca;
				}
			}
			return (Number(a.id) || 0) - (Number(b.id) || 0);
		});

		elList.innerHTML = '';
		items.slice(0, 16).forEach(function (ev) {
			var li = document.createElement('li');
			li.className = 'calendar-holidays__row ' + rowClass;
			var hd = parseISODate(ev.date);
			var label = pad(hd.getDate()) + ' ' + MONTH_NAMES[hd.getMonth()].toLowerCase();

			var dot = document.createElement('span');
			dot.className = 'calendar-holidays__mark ' + markClass;
			dot.setAttribute('aria-hidden', 'true');

			var text = document.createElement('div');
			text.className = 'calendar-holidays__row-text';
			var strong = document.createElement('strong');
			strong.textContent = (ev.title || '—') + ' · ' + label;
			text.appendChild(strong);
			if (ev.description) {
				var sub = document.createElement('span');
				sub.className = 'calendar-holidays__row-sub';
				sub.textContent = ev.description;
				text.appendChild(sub);
			}

			li.appendChild(dot);
			li.appendChild(text);
			elList.appendChild(li);
		});

		if (items.length === 0) {
			var empty = document.createElement('li');
			empty.className = 'calendar-holidays__empty';
			empty.textContent = strings[emptyKey] || emptyFallback;
			elList.appendChild(empty);
		}
	}

	function renderMeropList() {
		renderTypedEventList(
			elMeropList,
			'merop',
			'calendar-holidays__mark--merop',
			'calendar-holidays__row--merop',
			'emptyMerop',
			'Нет записей по запросу.'
		);
	}

	function renderFoundationList() {
		renderTypedEventList(
			elFoundationList,
			'foundation',
			'calendar-holidays__mark--foundation',
			'calendar-holidays__row--foundation',
			'emptyFoundation',
			'Нет записей по запросу.'
		);
	}

	function renderSideLists() {
		renderHolidaysList();
		renderMeropList();
		renderFoundationList();
	}

	if (elSearch) {
		elSearch.addEventListener('input', renderSideLists);
	}
	if (elSort) {
		elSort.addEventListener('change', renderSideLists);
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
	renderMonth();
	renderSideLists();
})();
