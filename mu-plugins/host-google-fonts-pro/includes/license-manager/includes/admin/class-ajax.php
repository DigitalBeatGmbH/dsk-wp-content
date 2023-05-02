<?php

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */

defined('ABSPATH') || exit;

class FFWPLM_Admin_AJAX
{
    /** @var string $plugin_text_domain */
    private $plugin_text_domain = 'ffwp-license-manager';

    /**
     * Actions & Hooks
     * 
     * @return void 
     */
    public function __construct()
    {
        add_action('wp_ajax_ffwp_license_manager_deactivate', [$this, 'deactivate_license']);
        add_action('wp_ajax_ffwp_license_manager_check', [$this, 'check_license']);
        add_action('wp_ajax_ffwp_license_manager_install_enc_key', [$this, 'install_encryption_key']);
    }

    /**
     * 
     */
    public function deactivate_license()
    {
        /*
        if (!isset($_POST['item_id'])) {
            wp_send_json_error(__('Plugin ID not set.', $this->plugin_text_domain));
        }

        $valid_licenses = FFWPLM::valid_licenses();
        $license_keys   = get_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY);
        $item_id        = sanitize_text_field($_POST['item_id']);
        $key            = sanitize_text_field($_POST['key']);
        $params         = [
            'edd_action' => 'deactivate_license',
            'license'    => FFWPLM::decrypt($key, $item_id),
            'item_id'    => $item_id,
            'url'        => home_url()
        ];
        $response       = wp_remote_post(
            apply_filters('ffwp_license_manager_api_url', ''),
            [
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $params
            ]
        );

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $message = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.', $this->plugin_text_domain);
            FFWPLM_Admin_Notice::set_notice($message, 'error');

            wp_send_json_error();
        }

        $response_body = json_decode(wp_remote_retrieve_body($response));
        $item_name     = $response_body->item_name;

        unset($valid_licenses[$item_id]);
        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_VALID_LICENSES, $valid_licenses);

        unset($license_keys[$item_id]);
        foreach ($license_keys as &$existing_key) {
            $existing_key['encrypted'] = true;
        }
        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY, $license_keys);

        if (!isset($response_body->success)) {
            FFWPLM_Admin_Notice::set_notice(sprintf(__('License for %s could not be deactivated. Maybe it already is deactivated?', $this->plugin_text_domain), $item_name), 'error');

            wp_send_json_error();
        }

        FFWPLM_Admin_Notice::set_notice(sprintf(__('License for %s successfully deactivated.', $this->plugin_text_domain), $item_name));

        delete_transient(FFWPLM_Admin::FFWP_LICENSE_MANAGER_NOTICE_COUNT);

        wp_send_json_success();
        */
    }

    /**
     * 
     * 
     * @return void 
     */
    public function check_license()
    {
        /*
        if (!isset($_POST['item_id'])) {
            wp_send_json_error(__('Plugin ID not set.', $this->plugin_text_domain));
        }

        $item_id  = sanitize_text_field($_POST['item_id']);
        $key      = sanitize_text_field($_POST['key']);
        $params   = [
            'edd_action' => 'check_license',
            'license'    => FFWPLM::decrypt($key, $item_id),
            'item_id'    => $item_id,
            'url'        => home_url()
        ];
        $response = wp_remote_post(
            apply_filters('ffwp_license_manager_api_url', 'https://daan.dev'),
            [
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $params
            ]
        );

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $message = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.', $this->plugin_text_domain);
            FFWPLM_Admin_Notice::set_notice($message, 'error');

            wp_send_json_error();
        }

        $response_body  = json_decode(wp_remote_retrieve_body($response));

       
        if ($response_body->success === false || $response_body->license === "invalid") {
            return;
        }

        $item_name      = $response_body->item_name;
        $valid_licenses = FFWPLM::valid_licenses();

        if (!isset($valid_licenses[$item_id])) {
            $message = sprintf(__('No license exists for %s.', $this->plugin_text_domain), $item_name);

            wp_send_json_error();
        }

        $updated_information = [
            'license_status' => $response_body->license,
            'expires'        => $response_body->expires
        ];

        $valid_licenses[$item_id] = array_replace($valid_licenses[$item_id], $updated_information);

        */

       //XXX update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_VALID_LICENSES, $valid_licenses);

       // delete_transient(FFWPLM_Admin::FFWP_LICENSE_MANAGER_NOTICE_COUNT);
    }

    /**
     * Runs a few checks to properly install the encryption key. If it fails, the message will reappear.
     */
    public function install_encryption_key()
    {
        FFWPLM::install_encryption_key();

        return wp_send_json_success();
    }
}
