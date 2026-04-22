(function ($) {
    $(function () {
        var frame;
        var $id = $('#portal_home_file_id');
        var $name = $('#portal_home_file_name');
        var $label = $('#portal_home_file_label');
        var $clear = $('#portal_home_file_clear');

        $('#portal_home_file_pick').on('click', function (e) {
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
                $name.text(att.filename || att.title || att.id);
                $label.show();
                $clear.show();
            });
            frame.open();
        });

        $clear.on('click', function (e) {
            e.preventDefault();
            $id.val('');
            $name.text('');
            $label.hide();
            $(this).hide();
        });
    });
})(jQuery);
