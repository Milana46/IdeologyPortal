(function ($) {
	'use strict';

	var frame;

	$('#portal-idl-pick-file').on('click', function (e) {
		e.preventDefault();
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: portalIdlAdmin && portalIdlAdmin.pickTitle ? portalIdlAdmin.pickTitle : '',
			button: { text: portalIdlAdmin && portalIdlAdmin.pickBtn ? portalIdlAdmin.pickBtn : 'OK' },
			multiple: false
		});
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$('#portal-idl-file').val(att.id);
			var name = att.filename || att.title || '';
			$('#portal-idl-file-name').text(name || '—');
		});
		frame.open();
	});

	$('#portal-idl-clear-file').on('click', function (e) {
		e.preventDefault();
		$('#portal-idl-file').val('');
		$('#portal-idl-file-name').text('—');
	});
})(jQuery);
