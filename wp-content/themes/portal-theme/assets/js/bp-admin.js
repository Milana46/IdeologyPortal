(function ($) {
	'use strict';

	var frame;
	var $id = $('#portal-bp-file-id');
	var $label = $('#portal-bp-file-label');

	$('#portal-bp-pick-file').on('click', function (e) {
		e.preventDefault();
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: 'Выберите файл',
			button: { text: 'Использовать этот файл' },
			multiple: false
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$id.val(att.id);
			$label.text(att.filename || att.title || att.url || '');
		});
		frame.open();
	});

	$('#portal-bp-clear-file').on('click', function (e) {
		e.preventDefault();
		$id.val('');
		$label.text('');
	});
})(jQuery);
