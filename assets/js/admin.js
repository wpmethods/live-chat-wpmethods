jQuery(document).ready(function($) {
    $('.color-field').wpColorPicker();

    $('.lc-tab-button').click(function() {
        var tab_id = $(this).data('tab');

        $('.lc-tab-button').removeClass('active');
        $('.lc-tab-content').removeClass('active');

        $(this).addClass('active');
        $('#tab-' + tab_id).addClass('active');
    });

    $('.color-field').on('change', function() {
        let color = $(this).val();
        $('#lc-live-preview').css('background', color);
    });
});
