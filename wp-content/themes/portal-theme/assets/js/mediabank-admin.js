(function ($) {
	'use strict';

	var frame;

	function syncGalleryHidden() {
		var ids = [];
		$('#portal-mb-gallery-list .portal-mb-gallery-item').each(function () {
			var id = $(this).attr('data-id');
			if (id) {
				ids.push(id);
			}
		});
		$('#portal-mb-gallery-ids').val(ids.join(','));
	}

	$('#portal-mb-pick-file').on('click', function (e) {
		e.preventDefault();
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: portalMbAdmin && portalMbAdmin.pickTitle ? portalMbAdmin.pickTitle : '',
			button: { text: portalMbAdmin && portalMbAdmin.pickBtn ? portalMbAdmin.pickBtn : 'OK' },
			multiple: false
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$('#portal-mb-file').val(att.id);
			var name = att.filename || att.title || '';
			$('#portal-mb-file-name').text(name || '—');
		});
		frame.open();
	});

	$('#portal-mb-clear-file').on('click', function (e) {
		e.preventDefault();
		$('#portal-mb-file').val('');
		$('#portal-mb-file-name').text('—');
	});

	if ($('#portal-mb-gallery-list').length) {
		$('#portal-mb-gallery-list').sortable({
			handle: '.portal-mb-gallery-item__handle',
			axis: 'y',
			update: syncGalleryHidden
		});

		$('#portal-mb-gallery-list').on('click', '.portal-mb-gallery-remove', function (e) {
			e.preventDefault();
			$(this).closest('li').remove();
			syncGalleryHidden();
		});

		var galFrame;
		$('#portal-mb-add-gallery').on('click', function (e) {
			e.preventDefault();
			if (!galFrame) {
				galFrame = wp.media({
					title: portalMbAdmin && portalMbAdmin.galTitle ? portalMbAdmin.galTitle : '',
					button: { text: portalMbAdmin && portalMbAdmin.galBtn ? portalMbAdmin.galBtn : 'OK' },
					library: { type: ['image', 'video'] },
					multiple: true
				});
				galFrame.on('select', function () {
					var sel = galFrame.state().get('selection');
					sel.each(function (model) {
						var j = model.toJSON();
						var id = j.id;
						if ($('#portal-mb-gallery-list .portal-mb-gallery-item[data-id="' + id + '"]').length) {
							return;
						}
						var mime = j.mime || '';
						var isVideo = j.type === 'video' || mime.indexOf('video/') === 0;
						var imgHtml = '';
						if (j.type === 'image' && j.sizes && j.sizes.thumbnail && j.sizes.thumbnail.url) {
							imgHtml =
								'<img src="' +
								j.sizes.thumbnail.url +
								'" alt="" style="width:48px;height:48px;object-fit:cover;" />';
						} else if (isVideo) {
							imgHtml =
								'<span class="portal-mb-gallery-item__vid-icon dashicons dashicons-video-alt3" aria-hidden="true"></span>';
						} else {
							imgHtml =
								'<span class="dashicons dashicons-media-default" aria-hidden="true"></span>';
						}
						var typeLabel = isVideo
							? portalMbAdmin && portalMbAdmin.videoLabel
								? portalMbAdmin.videoLabel
								: ''
							: portalMbAdmin && portalMbAdmin.photoLabel
								? portalMbAdmin.photoLabel
								: '';
						var name = j.filename || j.title || '';
						var $li = $('<li class="portal-mb-gallery-item" />').attr('data-id', String(id));
						$li.append(
							$('<span class="portal-mb-gallery-item__handle" aria-hidden="true" />').text('⋮⋮')
						);
						$li.append($('<span class="portal-mb-gallery-item__preview" />').html(imgHtml));
						$li.append(
							$('<span class="portal-mb-gallery-item__meta" />').append(
								$('<span class="portal-mb-gallery-item__type" />').text(typeLabel),
								$('<span class="portal-mb-gallery-item__name" />').text(name)
							)
						);
						$li.append(
							$(
								'<button type="button" class="button-link portal-mb-gallery-remove" />'
							).text(
								portalMbAdmin && portalMbAdmin.removeBtn ? portalMbAdmin.removeBtn : 'Remove'
							)
						);
						$('#portal-mb-gallery-list').append($li);
					});
					syncGalleryHidden();
				});
			}
			galFrame.open();
		});
	}
})(jQuery);
