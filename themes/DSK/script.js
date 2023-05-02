jQuery(document).ready(function ($) {

    let $target = '';
    let $times = 0;
    $('#elementor-editor-wrapper').click(function () {
        if($times > 1){
            return;
        }
        $('.e-global__popover-toggle').click(function () {
            $target = $(this);
        });

        $('.dialog-widget').click(function () {
            $target.parent().find('.pcr-button').click();
            $target.parent().find('.pcr-button').click();
        });
        $times ++;
    });

});