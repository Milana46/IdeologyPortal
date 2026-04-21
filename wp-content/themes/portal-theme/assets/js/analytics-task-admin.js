(function ($) {
	'use strict';

	function syncIds() {
		var ids = [];
		$('#portal-at-docs-list .portal-at-docs-item').each(function () {
			var id = $(this).attr('data-id');
			if (id) {
				ids.push(id);
			}
		});
		$('#portal-at-docs-ids').val(ids.join(','));
	}

	if ($('#portal-at-docs-list').length) {
		$('#portal-at-docs-list').sortable({
			handle: '.portal-at-docs-item__handle',
			axis: 'y',
			update: syncIds
		});

		$('#portal-at-docs-list').on('click', '.portal-at-docs-remove', function (e) {
			e.preventDefault();
			$(this).closest('li').remove();
			syncIds();
		});

		var frame;
		$('#portal-at-docs-add').on('click', function (e) {
			e.preventDefault();
			if (!frame) {
				frame = wp.media({
					title: portalAtAdmin && portalAtAdmin.pickTitle ? portalAtAdmin.pickTitle : '',
					button: { text: portalAtAdmin && portalAtAdmin.pickBtn ? portalAtAdmin.pickBtn : 'OK' },
					multiple: true
				});
				frame.on('select', function () {
					var sel = frame.state().get('selection');
					sel.each(function (model) {
						var j = model.toJSON();
						var id = j.id;
						if ($('#portal-at-docs-list .portal-at-docs-item[data-id="' + id + '"]').length) {
							return;
						}
						var name = j.filename || j.title || '';
						var $li = $('<li class="portal-at-docs-item" />').attr('data-id', String(id));
						$li.append(
							$('<span class="portal-at-docs-item__handle" aria-hidden="true" />').text('⋮⋮')
						);
						$li.append($('<span class="portal-at-docs-item__name" />').text(name));
						$li.append(
							$('<button type="button" class="button-link portal-at-docs-remove" />').text(
								portalAtAdmin && portalAtAdmin.removeBtn ? portalAtAdmin.removeBtn : 'Remove'
							)
						);
						$('#portal-at-docs-list').append($li);
					});
					syncIds();
				});
			}
			frame.open();
		});
	}
})(jQuery);
