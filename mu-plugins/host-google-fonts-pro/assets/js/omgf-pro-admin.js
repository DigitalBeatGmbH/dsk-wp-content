/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *            https://ffw.press
 * @copyright © 2022 Daan van den Bergh
 * @license   BY-NC-ND-4.0
 *            http://creativecommons.org/licenses/by-nc-nd/4.0/
 */

jQuery(document).ready(function ($) {
    var omgf_pro_admin = {
        /**
         * Bind events
         */
        init: function () {
            $('.omgf-optimize-fonts-manage .load-all').on('click', this.reset_load_all);
            $('.omgf-optimize-fonts-manage .preload').on('change', this.maybe_disable_replace);
            $('.omgf-optimize-fonts-manage .unload').on('change', this.maybe_uncheck_replace);
            $('.omgf-optimize-fonts-manage .replace').on('change', this.maybe_unload_all);
            $('.fallback-font-stack select').on('change', this.toggle_replace);
        },

        reset_load_all: function () {
            $replace_box = $(this).parents('tr.font-family').find('input.replace');

            $replace_box.attr('checked', false);
        },

        maybe_disable_replace: function () {
            var font_id = $(this).data('font-id'),
                $replace_box = $('tr[data-id="' + font_id + '"]').find('input.replace'),
                $fallback_box = $('tr[data-id="' + font_id + '"]').find('.fallback-font-stack select'),
                checked = $('input[data-font-id="' + font_id + '"].preload:checked').length;

            if (checked > 0) {
                $replace_box.attr('checked', false);
                $replace_box.attr('disabled', true);
            } else if (checked === 0 && $fallback_box.val() !== "") {
                $replace_box.attr('disabled', false);
            }
        },

        maybe_uncheck_replace: function () {
            var font_id = $(this).data('font-id'),
                $unloads = $('input[data-font-id="' + font_id + '"].unload'),
                checked = $('input[data-font-id="' + font_id + '"].unload:checked').length,
                total = $unloads.length,
                $replace_box = $('tr[data-id="' + font_id + '"]').find('input.replace'),
                $fallback_box = $('tr[data-id="' + font_id + '"]').find('.fallback-font-stack select');

            if (checked === total && $fallback_box.val() !== "") {
                // Do nothing.
            } else {
                $replace_box[0].checked = false;
            }
        },

        maybe_unload_all: function (e) {
            if (this.checked === true) {
                omgf_unload_all(e, this);

                this.checked = true;
            }
        },

        toggle_replace: function () {
            var $this = $(this),
                $parent_row = $this.parents('tr.font-family'),
                $replace_box = $parent_row.find('input.replace'),
                $preloads = $('input[data-font-id="' + $parent_row.data('id') + '"].preload'),
                preload_checked = false;

            $preloads.each(function (index, elem) {
                if (elem.checked === true) {
                    preload_checked = true;

                    return false;
                }
            });

            if (this.value !== '' && preload_checked !== true) {
                $replace_box.attr('disabled', false);
            } else {
                $replace_box.attr('disabled', true);
                $replace_box[0].checked = false;
            }
        }
    };

    omgf_pro_admin.init();
});
