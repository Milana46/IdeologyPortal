(function () {
	'use strict';

	var listEl = document.getElementById('sov-list');
	var search = document.getElementById('sov-search');
	var sortSel = document.getElementById('sov-sort');
	var tabBtns = document.querySelectorAll('.sov-tabs__btn');
	var emptyEl = document.getElementById('sov-empty');

	var viewModal = document.getElementById('sov-view-modal');
	var viewClose = document.getElementById('sov-view-close');
	var viewTitle = document.getElementById('sov-view-title');
	var readingWrap = document.getElementById('sov-view-reading-wrap');
	var readingLabel = document.getElementById('sov-view-reading-label');
	var readingEl = document.getElementById('sov-view-reading');
	var previewWrap = document.getElementById('sov-view-preview-wrap');
	var viewFrame = document.getElementById('sov-view-frame');
	var viewImg = document.getElementById('sov-view-img');
	var viewVid = document.getElementById('sov-view-vid');
	var viewFallback = document.getElementById('sov-view-fallback');
	var viewDownload = document.getElementById('sov-view-download');

	var state = {
		tab: 'all',
		query: ''
	};

	if (tabBtns.length) {
		var a0 = document.querySelector('.sov-tabs__btn.is-active');
		if (a0) {
			var t0 = a0.getAttribute('data-filter');
			if (t0) {
				state.tab = t0;
			}
		}
	}

	function getCards() {
		if (!listEl) {
			return [];
		}
		return Array.prototype.slice.call(listEl.querySelectorAll('.sov-card'));
	}

	function normalize(s) {
		return (s || '').trim().toLowerCase();
	}

	function sortCards() {
		if (!listEl) {
			return;
		}
		var mode = sortSel ? sortSel.value : 'date-desc';
		var cards = getCards();
		if (mode === 'title-asc') {
			cards.sort(function (a, b) {
				return (a.getAttribute('data-sov-title') || '').localeCompare(
					b.getAttribute('data-sov-title') || '',
					'ru'
				);
			});
		} else {
			cards.sort(function (a, b) {
				var ta = parseInt(a.getAttribute('data-sov-sort') || '0', 10);
				var tb = parseInt(b.getAttribute('data-sov-sort') || '0', 10);
				return mode === 'date-asc' ? ta - tb : tb - ta;
			});
		}
		cards.forEach(function (c) {
			listEl.appendChild(c);
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
			var type = card.getAttribute('data-sov-category') || '';
			var titleHay = (card.getAttribute('data-sov-search') || '').toLowerCase();
			var matchType = filter === 'all' || type === filter;
			var matchQ = !q || titleHay.indexOf(q) !== -1;
			var show = matchType && matchQ;
			card.classList.toggle('sov-card--filtered-out', !show);
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
	}

	tabBtns.forEach(function (btn) {
		btn.addEventListener('click', function () {
			tabBtns.forEach(function (b) {
				b.classList.remove('is-active');
				b.setAttribute('aria-selected', 'false');
			});
			btn.classList.add('is-active');
			btn.setAttribute('aria-selected', 'true');
			var f = btn.getAttribute('data-filter');
			if (f) {
				state.tab = f;
			}
			refresh();
		});
	});

	if (search) {
		search.addEventListener('input', function () {
			state.query = search.value;
			sortCards();
			applyFilter();
		});
	}

	if (sortSel) {
		sortSel.addEventListener('change', refresh);
	}

	function hideViewMedia() {
		if (viewImg) {
			viewImg.hidden = true;
			viewImg.removeAttribute('src');
			viewImg.removeAttribute('width');
			viewImg.removeAttribute('height');
		}
		if (viewVid) {
			viewVid.hidden = true;
			try {
				viewVid.pause();
			} catch (e) {}
			viewVid.removeAttribute('src');
			if (typeof viewVid.load === 'function') {
				viewVid.load();
			}
		}
	}

	function closeViewModal() {
		hideViewMedia();
		if (viewDownload) {
			viewDownload.hidden = true;
			viewDownload.setAttribute('href', '#');
			viewDownload.removeAttribute('download');
		}
		if (viewFrame) {
			viewFrame.src = 'about:blank';
			viewFrame.hidden = false;
		}
		if (viewFallback) {
			viewFallback.hidden = true;
			viewFallback.textContent = '';
		}
		if (readingWrap) {
			readingWrap.hidden = true;
		}
		if (readingEl) {
			readingEl.textContent = '';
		}
		if (previewWrap) {
			previewWrap.hidden = true;
		}
		if (viewModal) {
			viewModal.classList.remove('sov-view-modal--preview');
			if (typeof viewModal.close === 'function') {
				viewModal.close();
			} else {
				viewModal.removeAttribute('open');
			}
		}
	}

	function openViewModal(data) {
		if (!viewModal || !data) {
			return;
		}
		var title = data.title || '';
		var excerpt = typeof data.excerpt === 'string' ? data.excerpt : '';
		var viewer = (data.viewer || 'reading').trim();
		var docUrl = data.docUrl || '';
		var pdfUrl = data.pdfUrl || '';
		var fileName = data.fileName || '';

		if (
			readingLabel &&
			typeof portalSovetnik !== 'undefined' &&
			portalSovetnik.strings &&
			portalSovetnik.strings.readingLabel
		) {
			readingLabel.textContent = portalSovetnik.strings.readingLabel;
		}

		if (viewTitle) {
			viewTitle.textContent = title;
		}

		var excerptTrim = excerpt.replace(/^\s+|\s+$/g, '');
		if (readingWrap && readingEl) {
			if (excerptTrim) {
				readingWrap.hidden = false;
				readingEl.textContent = excerpt;
			} else {
				readingWrap.hidden = true;
				readingEl.textContent = '';
			}
		}

		if (viewDownload) {
			if (docUrl) {
				viewDownload.hidden = false;
				viewDownload.href = docUrl;
				if (fileName) {
					viewDownload.setAttribute('download', fileName);
				} else {
					viewDownload.removeAttribute('download');
				}
				if (
					typeof portalSovetnik !== 'undefined' &&
					portalSovetnik.strings &&
					portalSovetnik.strings.download
				) {
					viewDownload.textContent = portalSovetnik.strings.download;
				}
			} else {
				viewDownload.hidden = true;
				viewDownload.setAttribute('href', '#');
				viewDownload.removeAttribute('download');
			}
		}

		hideViewMedia();
		if (viewFallback) {
			viewFallback.hidden = true;
			viewFallback.textContent = '';
		}
		if (viewFrame) {
			viewFrame.removeAttribute('src');
			viewFrame.setAttribute('title', title || '');
		}

		var showPreview = false;

		if (docUrl && viewer !== 'reading') {
			if (previewWrap) {
				previewWrap.hidden = false;
			}
			if (viewer === 'image' && viewImg) {
				if (viewFrame) {
					viewFrame.hidden = true;
				}
				viewImg.hidden = false;
				viewImg.src = docUrl;
				viewImg.alt = title || '';
				showPreview = true;
			} else if (viewer === 'video' && viewVid) {
				if (viewFrame) {
					viewFrame.hidden = true;
				}
				viewVid.hidden = false;
				viewVid.src = docUrl;
				showPreview = true;
			} else if (viewer === 'pdf') {
				var pdfSrc = pdfUrl || docUrl;
				if (viewFrame) {
					viewFrame.hidden = false;
					viewFrame.src = pdfSrc;
				}
				showPreview = true;
			} else if (viewer === 'office' && docUrl) {
				if (viewFrame) {
					viewFrame.hidden = false;
					viewFrame.src =
						'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(docUrl);
				}
				showPreview = true;
			} else {
				if (viewFrame) {
					viewFrame.hidden = true;
					viewFrame.removeAttribute('src');
				}
				if (viewFallback) {
					viewFallback.hidden = false;
					viewFallback.textContent =
						typeof portalSovetnik !== 'undefined' &&
						portalSovetnik.strings &&
						portalSovetnik.strings.previewUnavailable
							? portalSovetnik.strings.previewUnavailable
							: '';
				}
			}
		} else if (previewWrap) {
			previewWrap.hidden = true;
		}

		if (viewModal) {
			viewModal.classList.toggle('sov-view-modal--preview', showPreview);
			if (typeof viewModal.showModal === 'function') {
				viewModal.showModal();
			} else {
				viewModal.setAttribute('open', 'open');
			}
		}
	}

	function handleOpenClick(e) {
		var opener = e.target.closest('.sov-card__open, .sov-sidebar-open');
		if (!opener) {
			return;
		}
		e.preventDefault();
		var raw = opener.getAttribute('data-open');
		if (!raw) {
			return;
		}
		try {
			var payload = JSON.parse(raw);
			openViewModal(payload);
		} catch (err) {
			return;
		}
	}

	document.addEventListener('click', handleOpenClick);

	if (viewModal) {
		if (viewClose) {
			viewClose.addEventListener('click', closeViewModal);
		}
		viewModal.addEventListener('click', function (e) {
			if (e.target === viewModal) {
				closeViewModal();
			}
		});
		document.addEventListener('keydown', function (e) {
			if (
				e.key === 'Escape' &&
				viewModal &&
				(viewModal.open || viewModal.hasAttribute('open'))
			) {
				closeViewModal();
			}
		});
	}

	refresh();
})();
