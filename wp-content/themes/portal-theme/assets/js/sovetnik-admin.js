(function ($) {
	'use strict';

	var frame;

	$('#portal-sv-pick-file').on('click', function (e) {
		e.preventDefault();
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: portalSvAdmin && portalSvAdmin.pickTitle ? portalSvAdmin.pickTitle : '',
			button: { text: portalSvAdmin && portalSvAdmin.pickBtn ? portalSvAdmin.pickBtn : 'OK' },
			multiple: false
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$('#portal-sv-file').val(att.id);
			var name = att.filename || att.title || '';
			$('#portal-sv-file-name').text(name || '—');
		});
		frame.open();
	});

	$('#portal-sv-clear-file').on('click', function (e) {
		e.preventDefault();
		$('#portal-sv-file').val('');
		$('#portal-sv-file-name').text('—');
	});
})(jQuery);
