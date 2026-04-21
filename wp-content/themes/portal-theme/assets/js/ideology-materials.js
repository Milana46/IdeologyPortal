(function () {
	'use strict';

	var cardsRoot = document.getElementById('ideology-cards');
	var searchInput = document.getElementById('ideology-search');
	var tabBtns = document.querySelectorAll('.ideology-tabs [data-ideology-tab]');

	var viewModal = document.getElementById('ideology-view-modal');
	var viewClose = document.getElementById('ideology-view-close');
	var viewTitle = document.getElementById('ideology-view-title');
	var readingWrap = document.getElementById('ideology-view-reading-wrap');
	var readingLabel = document.getElementById('ideology-view-reading-label');
	var readingEl = document.getElementById('ideology-view-reading');
	var previewWrap = document.getElementById('ideology-view-preview-wrap');
	var viewFrame = document.getElementById('ideology-view-frame');
	var viewImg = document.getElementById('ideology-view-img');
	var viewVid = document.getElementById('ideology-view-vid');
	var viewFallback = document.getElementById('ideology-view-fallback');
	var viewDownload = document.getElementById('ideology-view-download');

	var state = {
		tab: 'all',
		query: ''
	};

	if (tabBtns.length) {
		var activeInit = document.querySelector('.ideology-tabs [data-ideology-tab].is-active');
		if (activeInit) {
			var t0 = activeInit.getAttribute('data-ideology-tab');
			if (t0) {
				state.tab = t0;
			}
		}
	}

	function normalize(s) {
		return (s || '').trim().toLowerCase();
	}

	function applyFilters() {
		var tab = state.tab || 'all';
		var q = normalize(state.query);
		if (!cardsRoot) {
			return;
		}
		cardsRoot.querySelectorAll('.ideology-card').forEach(function (card) {
			var cat = card.getAttribute('data-ideology-category') || '';
			var hay = normalize(card.getAttribute('data-ideology-search') || '');
			var tabOk = tab === 'all' || cat === tab;
			var qOk = !q || hay.indexOf(q) !== -1;
			card.classList.toggle('ideology-card--filtered-out', !(tabOk && qOk));
		});
	}

	tabBtns.forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			var tab = btn.getAttribute('data-ideology-tab');
			if (!tab) {
				return;
			}
			tabBtns.forEach(function (b) {
				b.classList.remove('is-active');
			});
			btn.classList.add('is-active');
			state.tab = tab;
			applyFilters();
		});
	});

	if (searchInput) {
		searchInput.addEventListener('input', function () {
			state.query = searchInput.value;
			applyFilters();
		});
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
			viewModal.classList.remove('ideology-view-modal--preview');
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

		if (readingLabel && typeof portalIdeology !== 'undefined' && portalIdeology.strings && portalIdeology.strings.readingLabel) {
			readingLabel.textContent = portalIdeology.strings.readingLabel;
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
				if (typeof portalIdeology !== 'undefined' && portalIdeology.strings && portalIdeology.strings.download) {
					viewDownload.textContent = portalIdeology.strings.download;
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
						typeof portalIdeology !== 'undefined' &&
						portalIdeology.strings &&
						portalIdeology.strings.previewUnavailable
							? portalIdeology.strings.previewUnavailable
							: '';
				}
			}
		} else if (previewWrap) {
			previewWrap.hidden = true;
		}

		if (viewModal) {
			viewModal.classList.toggle('ideology-view-modal--preview', showPreview);
			if (typeof viewModal.showModal === 'function') {
				viewModal.showModal();
			} else {
				viewModal.setAttribute('open', 'open');
			}
		}
	}

	if (cardsRoot) {
		cardsRoot.addEventListener('click', function (e) {
			var openBtn = e.target.closest('.ideology-card__open');
			if (!openBtn || !cardsRoot.contains(openBtn)) {
				return;
			}
			e.preventDefault();
			var raw = openBtn.getAttribute('data-open');
			if (!raw) {
				return;
			}
			try {
				var payload = JSON.parse(raw);
				openViewModal(payload);
			} catch (err) {
				return;
			}
		});
	}

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

	applyFilters();
})();
