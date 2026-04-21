(function () {
	'use strict';

	var modal = document.getElementById('analytics-task-modal');
	if (!modal) {
		return;
	}
	var shell = modal.querySelector('.analytics-modal__shell');
	var backdrop = modal.querySelector('.analytics-modal__backdrop');

	function closeModal() {
		if (!modal) {
			return;
		}
		modal.hidden = true;
		if (shell) {
			shell.innerHTML = '';
		}
		document.body.style.overflow = '';
	}

	function openModal(taskId) {
		if (!shell || !taskId) {
			return;
		}
		var tmpl = document.getElementById('analytics-task-detail-' + taskId);
		if (!tmpl || !tmpl.content) {
			return;
		}
		shell.innerHTML = '';
		shell.appendChild(tmpl.content.cloneNode(true));
		modal.hidden = false;
		document.body.style.overflow = 'hidden';

		var closeBtn = shell.querySelector('.analytics-modal__close');
		if (closeBtn) {
			closeBtn.addEventListener('click', closeModal);
		}
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.analytics-task-card__show');
		if (btn) {
			e.preventDefault();
			var card = btn.closest('.analytics-task-card');
			var id = card ? card.getAttribute('data-task-id') : '';
			openModal(id);
		}
	});

	if (backdrop) {
		backdrop.addEventListener('click', closeModal);
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && modal && !modal.hidden) {
			closeModal();
		}
	});
})();
