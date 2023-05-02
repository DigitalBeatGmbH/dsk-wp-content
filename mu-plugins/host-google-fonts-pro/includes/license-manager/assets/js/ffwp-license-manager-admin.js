/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */

jQuery(document).ready(function ($) {
    var ffwp_license_manager = {
        init: function () {
            $('.ffwp-deactivate-license').on('click', this.deactivate);
            $('.check-license').on('click', this.check);
            $('#ffwp_install_enc_key').on('click', this.install_enc_key);
        },

        /**
         * Trigger deactivate method to remove key from db and call deactivate API.
         */
        deactivate: function () {
            var self = this;

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    key: $(self).data('key'),
                    item_id: $(self).data('item-id'),
                    action: 'ffwp_license_manager_deactivate'
                },
                complete: function () {
                    location.reload();
                }
            });
        },

        check: function () {
            var self = this;

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    key: $(self).data('key'),
                    item_id: $(self).data('item-id'),
                    action: 'ffwp_license_manager_check'
                },
                complete: function () {
                    location.reload();
                }
            });
        },

        install_enc_key: function () {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'ffwp_license_manager_install_enc_key'
                },
                complete: function () {
                    /**
                     * Hack to make sure the notice is expired.
                     */
                    setTimeout('location.reload()', 5000);
                }
            });
        }
    }
    ffwp_license_manager.init();
});