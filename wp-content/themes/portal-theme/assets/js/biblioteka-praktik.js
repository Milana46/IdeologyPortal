(function () {
	'use strict';

	var modal = document.getElementById('bp-doc-modal');
	var frame = document.getElementById('bp-doc-frame');
	var mediaImg = document.getElementById('bp-media-img');
	var mediaVid = document.getElementById('bp-media-vid');
	var fallback = document.getElementById('bp-doc-fallback');
	var fbLocal = document.getElementById('bp-doc-fallback-local');
	var fbGeneric = document.getElementById('bp-doc-fallback-generic');
	var titleEl = document.getElementById('bp-doc-modal-title');
	var nameEl = modal ? modal.querySelector('.bp-doc-modal__filename') : null;
	var closeBtn = modal ? modal.querySelector('.bp-doc-modal__close') : null;
	var dl = document.getElementById('bp-doc-download');

	function isModalLocal() {
		return modal && modal.getAttribute('data-bp-is-local') === '1';
	}

	function hideMediaExtras() {
		if (mediaImg) {
			mediaImg.hidden = true;
			mediaImg.removeAttribute('src');
		}
		if (mediaVid) {
			mediaVid.hidden = true;
			try {
				mediaVid.pause();
			} catch (e) {}
			mediaVid.removeAttribute('src');
			if (typeof mediaVid.load === 'function') {
				mediaVid.load();
			}
		}
	}

	function openModalFromButton(btn) {
		if (!modal) {
			return;
		}
		var docUrl = btn.getAttribute('data-doc-url') || '';
		var pdfUrl = btn.getAttribute('data-pdf-url') || '';
		var title = btn.getAttribute('data-doc-title') || '';
		var fileName = btn.getAttribute('data-doc-name') || '';
		var mode = (btn.getAttribute('data-media-viewer') || '').trim();
		if (!docUrl) {
			return;
		}

		if (!mode) {
			var localFallback = isModalLocal();
			if (pdfUrl) {
				mode = 'pdf';
			} else if (docUrl && !localFallback) {
				mode = 'office';
			} else {
				mode = 'fallback';
			}
		}

		if (titleEl) {
			titleEl.textContent = title;
		}
		if (nameEl) {
			nameEl.textContent = fileName;
		}
		if (dl) {
			dl.href = docUrl;
			if (fileName) {
				dl.setAttribute('download', fileName);
			} else {
				dl.removeAttribute('download');
			}
		}

		var local = isModalLocal();

		hideMediaExtras();

		if (frame) {
			frame.removeAttribute('src');
			frame.setAttribute('title', title || '');
		}
		if (fallback) {
			fallback.hidden = true;
		}
		if (fbLocal) {
			fbLocal.classList.toggle('bp-hidden', !local);
		}
		if (fbGeneric) {
			fbGeneric.classList.toggle('bp-hidden', local);
		}

		var showPreview = false;

		if (mode === 'image' && mediaImg) {
			if (frame) {
				frame.hidden = true;
			}
			mediaImg.hidden = false;
			mediaImg.src = docUrl;
			mediaImg.alt = title || '';
			showPreview = true;
		} else if (mode === 'video' && mediaVid) {
			if (frame) {
				frame.hidden = true;
			}
			mediaVid.hidden = false;
			mediaVid.src = docUrl;
			showPreview = true;
		} else if (mode === 'pdf') {
			var pdfSrc = pdfUrl || docUrl;
			if (frame) {
				frame.hidden = false;
				frame.src = pdfSrc;
			}
			showPreview = true;
		} else if (mode === 'office' && docUrl && !local) {
			if (frame) {
				frame.hidden = false;
				frame.src = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(docUrl);
			}
			showPreview = true;
		} else {
			if (frame) {
				frame.hidden = true;
				frame.removeAttribute('src');
			}
			if (fallback) {
				fallback.hidden = false;
			}
		}

		if (modal) {
			modal.classList.toggle('bp-doc-modal--preview', showPreview);
			if (typeof modal.showModal === 'function') {
				modal.showModal();
			} else {
				modal.setAttribute('open', 'open');
			}
		}
	}

	function closeDocModal() {
		hideMediaExtras();
		if (frame) {
			frame.src = 'about:blank';
			frame.hidden = false;
		}
		if (fallback) {
			fallback.hidden = true;
		}
		if (modal) {
			if (typeof modal.close === 'function') {
				modal.close();
			} else {
				modal.removeAttribute('open');
			}
		}
	}

	document.addEventListener('click', function (e) {
		var opener = e.target.closest('.bp-open-doc');
		if (opener) {
			e.preventDefault();
			openModalFromButton(opener);
		}
	});

	if (modal) {
		if (closeBtn) {
			closeBtn.addEventListener('click', closeDocModal);
		}

		modal.addEventListener('click', function (e) {
			if (e.target === modal) {
				closeDocModal();
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && modal && (modal.open || modal.hasAttribute('open'))) {
				closeDocModal();
			}
		});
	}

	
	var categoryTabsRoot = document.getElementById('bp-category-tabs');
	var searchInput = document.getElementById('bp-search');
	var sortSelect = document.getElementById('bp-sort');

	var state = {
		tab: 'all',
		query: ''
	};

	if (categoryTabsRoot) {
		var activeTabInit = categoryTabsRoot.querySelector('.kse-tabs__btn.is-active');
		if (activeTabInit) {
			var at0 = activeTabInit.getAttribute('data-tab');
			if (at0) {
				state.tab = at0;
			}
		}
	}

	function normalize(s) {
		return (s || '').trim().toLowerCase();
	}

	function applyFilters() {
		var q = normalize(state.query);
		var tab = state.tab || 'all';
		var list = document.getElementById('bp-list');
		if (!list) {
			return;
		}
		var els = list.querySelectorAll('.bp-card');
		els.forEach(function (el) {
			var cat = el.getAttribute('data-bp-category') || '';
			var hay = normalize(el.getAttribute('data-bp-search') || '');
			var typeOk = tab === 'all' || cat === tab;
			var searchOk = !q || hay.indexOf(q) !== -1;
			var show = typeOk && searchOk;
			el.classList.toggle('bp-card--filtered-out', !show);
			el.hidden = !show;
		});
	}

	function applySort() {
		var mode = (sortSelect && sortSelect.value) || 'date-desc';
		var list = document.getElementById('bp-list');
		if (!list) {
			return;
		}
		var cards = Array.prototype.slice.call(list.querySelectorAll('.bp-card'));
		cards.sort(function (a, b) {
			if (mode === 'title-asc') {
				var ta = (a.querySelector('.bp-card__title') || {}).textContent || '';
				var tb = (b.querySelector('.bp-card__title') || {}).textContent || '';
				var c = ta.localeCompare(tb, 'ru');
				if (c !== 0) {
					return c;
				}
			}
			var oa = parseInt(a.getAttribute('data-bp-order') || '0', 10);
			var ob = parseInt(b.getAttribute('data-bp-order') || '0', 10);
			if (mode === 'date-asc') {
				return oa - ob;
			}
			return ob - oa;
		});
		cards.forEach(function (node) {
			list.appendChild(node);
		});
	}

	function renderMaterials() {
		applyFilters();
		applySort();
	}

	function activateCategoryTab(btn) {
		if (!categoryTabsRoot) {
			return;
		}
		var tab = btn.getAttribute('data-tab');
		if (!tab) {
			return;
		}
		categoryTabsRoot.querySelectorAll('.kse-tabs__btn').forEach(function (b) {
			b.classList.remove('is-active');
			b.setAttribute('aria-selected', 'false');
		});
		btn.classList.add('is-active');
		btn.setAttribute('aria-selected', 'true');
		state.tab = tab;
		renderMaterials();
	}
	if (categoryTabsRoot) {
		categoryTabsRoot.addEventListener('click', function (e) {
			var btn = e.target.closest('.kse-tabs__btn');
			if (!btn || !categoryTabsRoot.contains(btn)) {
				return;
			}
			e.preventDefault();
			activateCategoryTab(btn);
		});
	}

	if (searchInput) {
		searchInput.addEventListener('input', function () {
			state.query = searchInput.value;
			renderMaterials();
		});
	}

	if (sortSelect) {
		sortSelect.addEventListener('change', function () {
			applySort();
		});
	}

	renderMaterials();
})();
