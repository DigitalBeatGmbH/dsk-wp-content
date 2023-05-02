<?php

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */

defined('ABSPATH') || exit;

/**
 * Class FFWPLM_Admin_Functions
 */
class FFWPLM_Admin_Functions
{
    /** @var string $plugin_text_domain */
    private $plugin_text_domain = 'ffwp-license-manager';

    /**
     * FFWPLM_Admin_Functions constructor.
     */
    public function __construct()
    {
        add_filter('pre_update_option_' . FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY, [$this, 'encrypt_license_key_settings'], 10, 1);
        add_action('admin_init', [$this, 'activate_license_keys']);
        add_action('admin_action_update', [$this, 'update_post']);
    }

    /**
     * @param mixed $items 
     * @return mixed 
     */
    public function encrypt_license_key_settings($items)
    {
        if ($items == null) {
            return get_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY);
        }

        foreach ($items as &$item) {
            if (!$item['key'] || isset($item['encrypted'])) {
                continue;
            }

            $item['key'] = FFWPLM::encrypt($item['key']);
        }

        return $items;
    }

    /**
     *
     */
    public function activate_license_keys()
    {
        if (!isset($_POST[FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY])) {
            return;
        }

        if (!check_admin_referer(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTINGS_NONCE, FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTINGS_NONCE)) {
            return;
        }

        $license_keys   = $_POST[FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY];
        $valid_licenses = FFWPLM::valid_licenses();

        foreach ($license_keys as $id => $license) {
            $license_data = $this->activate_license($license['key'], $id);

            /**
             * If no license data was returned. Skip out early.
             */
            if (!$license_data || empty((array) $license_data) || !$license_data->success) {
                continue;
            }

            /**
             * Contains all required data for automatic updates.
             *
             * @var array $valid_licenses
             */
            $valid_licenses[$id] = [
                'license_status' => $license_data->license,           // Possible values: (string) 'valid' | (string) 'invalid'
                'license'        => FFWPLM::encrypt($license['key']), // Entered key.
                'expires'        => $license_data->expires ?? null,   // Not present when request failed.
                'plugin_file'    => $license['plugin_file'],          // Added by plugin.
            ];
        }

        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_VALID_LICENSES, $valid_licenses);

        // Reset transient.
        delete_transient(FFWPLM_Admin::FFWP_LICENSE_MANAGER_NOTICE_COUNT);
    }

    /**
     * @param $key
     * @param $item_id
     *
     * @return stdClass
     */
    private function activate_license($key, $item_id)
    {
       /*
        if (!$key) {
            return (object) [];
        }

        $params = [
            'edd_action' => 'activate_license',
            'license'    => $key,
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

        $message = '';

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $message = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : sprintf(__('An error occurred, contact support and include the following message: %s - %s', $this->plugin_text_domain), wp_remote_retrieve_response_code($response), wp_remote_retrieve_body($response));

            FFWPLM_Admin_Notice::set_notice($message, 'error');

            return (object) [];
        }

        $license_data = json_decode(wp_remote_retrieve_body($response));

        if ($license_data !== null && $license_data->success === false) {
            if ($license_data->error == 'expired') {
                $message = $this->generate_message($license_data->error, $license_data->item_name, $license_data->expires, $item_id, $key);
            } else {
                $message = $this->generate_message($license_data->error, $license_data->item_name);
            }
        }

        if (!empty($message)) {
            FFWPLM_Admin_Notice::set_notice($message, 'error');
        }

        return $license_data;
        */
    }

    /**
     * Generate error message. 
     * 
     * @param mixed $error_code 
     * @param mixed $plugin_name 
     * @param mixed|null $expires 
     * @param string $item_id 
     * @param string $key 
     * @return string 
     */
    private function generate_message($error_code, $plugin_name, $expires = null, $item_id = '', $key = '')
    {
        switch ($error_code) {
            case 'expired':
                $message = sprintf(__('Your license key expired on %s. <a href="%s" target="_blank">Click here to renew</a>.', $this->plugin_text_domain), date_i18n(get_option('date_format'), strtotime($expires, current_time('timestamp'))), sprintf(FFWPLM::FFW_PRESS_URL_RENEW_LICENSE, $key, $item_id));
                break;
            case 'revoked':
                $message = __('Your license key has been disabled.', $this->plugin_text_domain);
                break;
            case 'missing':
                $message = sprintf(__('License key doesn\'t exist. Purchase a license key on <a href="%s" target="_blank">Daan.dev</a>.', $this->plugin_text_domain), FFWPLM::FFW_PRESS_URL_WORDPRESS_PLUGINS);
                break;
            case 'invalid':
            case 'site_inactive':
                $message = __('Your license is not active for this URL.', $this->plugin_text_domain);
                break;
            case 'item_name_mismatch':
                $message = sprintf(__('This appears to be an invalid license key for %s.', $this->plugin_text_domain), $plugin_name);
                break;
            case 'no_activations_left':
                $message = sprintf(__('You\'ve reached your limit for your license. Visit <a href="%s" target="_blank">your Account area</a> to upgrade your license.', $this->plugin_text_domain), FFWPLM::FFW_PRESS_URL_LICENSE_KEYS);
                break;
            default:
                $message = sprintf(__('An unexpected error occurred. Please <a href="%s">contact me</a>.', $this->plugin_text_domain), FFWPLM::FFW_PRESS_URL_CONTACT);
                break;
        }

        return $message;
    }

    /**
     * Merge existing keys with newly added keys.
     * 
     * @return void 
     */
    public function update_post()
    {
        if (isset($_POST['option_page']) && $_POST['option_page'] != 'ffwp-license-manager') {
            return;
        }

        if (!isset($_POST[FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY])) {
            return;
        }

        $existing_keys = get_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY) ?: [];
        foreach ($existing_keys as &$key) {
            $key['encrypted'] = true;
        }

        $_POST[FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY] = $_POST[FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY] + $existing_keys;
    }
}
