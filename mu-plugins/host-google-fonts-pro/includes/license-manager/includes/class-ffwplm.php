<?php

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */

defined('ABSPATH') || exit;

class FFWPLM
{
    const FFW_PRESS_URL_API               = '';
    const FFW_PRESS_URL_WORDPRESS_PLUGINS = '';
    const FFW_PRESS_URL_LICENSE_KEYS      = '';
    const FFW_PRESS_URL_RENEW_LICENSE     = '';
    const FFW_PRESS_URL_CONTACT           = '';
    const DAAN_LM_ENC_KEY_LABEL           = 'DAAN_LICENSE_ENC_KEY';
    const FFWP_ENCRYPTION_METHOD          = 'AES-128-CTR';

    /**
     * FFWPLM constructor.
     */
    public function __construct()
    {
        $this->generate_cypher();

        add_action('wp_loaded', [$this, 'do_updater']);

        if (!is_admin()) {
            return;
        }

        $this->init();
    }

    /**
     * Generates cypher used for encryption and stores it into the database.
     */
    private function generate_cypher()
    {
        /**
         * @since v1.10.3 No need to go through all that when the cypher is
         *                already defined.
         */
        if (defined('FFWPLM_CYPHER')) {
            return;
        }

        $cypher = get_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_CYPHER);

        if (!$cypher) {
            $cypher = bin2hex(random_bytes(8));

            update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_CYPHER, $cypher);
        }

        define('FFWPLM_CYPHER', $cypher);
    }

    /**
     * Check for updates for all installed plugins, incl. this (free) plugin.
     */
    public function do_updater()
    {
        /*
        foreach (self::valid_licenses() as $id => $license_data) {
            if ($license_data['license_status'] !== 'valid') {
                continue;
            }

            if (!file_exists($license_data['plugin_file'])) {
                continue;
            }

            $plugin_file = $license_data['plugin_file'] ?? '';
            $plugin_data = file_get_contents($plugin_file);
            preg_match('/(?<=Version:[\s\S])([0-9\.]+)(?=$)/m', $plugin_data, $plugin_version, PREG_UNMATCHED_AS_NULL);

            if (empty($plugin_version) || !is_string($plugin_version[0])) {
                continue;
            }

            $license_key = self::decrypt($license_data['license'], $id);

            if (!$license_key) {
                continue;
            }

            $plugin_version = reset($plugin_version);

            new FFWPLM_Updater(
                apply_filters('ffwp_license_manager_api_url', 'https://daan.dev'),
                $plugin_file,
                [
                    'license'   => $license_key,
                    'item_id'   => $id,
                    'version'   => $plugin_version,
                    'author'    => 'Daan van den Bergh',
                    'url'       => home_url(),
                    'beta'      => false
                ]
            );
        }
        */
    }

    /**
     * Fetches all previously validated licenses from the database.
     * 
     * @return array
     */
    public static function valid_licenses()
    {
        static $valid_licenses = [];

        if (empty($valid_licenses)) {
            $valid_licenses = get_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_VALID_LICENSES, []) ?: [];
        }

        return $valid_licenses;
    }

    /**
     * Decrypt $key (and validate) before returning the result.
     * 
     * @param string $key Encrypted string.
     * @param int    $id  Download ID.
     * 
     * @return string 
     */
    public static function decrypt($key, $id)
    {
        /**
         * @since v1.10.3 Store decrypted keys in a static array to prevent duplicate decrypts.
         */
        static $decrypted_keys;

        if (is_array($decrypted_keys) && isset($decrypted_keys[$id])) {
            return $decrypted_keys[$id];
        }

        if (!defined(self::DAAN_LM_ENC_KEY_LABEL)) {
           // FFWPLM_Admin_Notice::set_notice(sprintf(__('Your Daan.dev license(s) failed to validate, because required encryption keys are missing. Visit the <a href="%s">Manage Licenses</a> page to fix it. If this message reappears, try reloading the page, otherwise <a href="%s">fix it manually</a>.', 'ffwp-license-manager'), admin_url('options-general.php?page=ffwp-license-manager'), 'https://daan.dev/docs/getting-started/activate-license/#encryption-key'), 'error', 'ffwp-encryption-key-missing', 'all', 1);

            return '';
        }

        $decrypted_keys[$id] = openssl_decrypt($key, FFWPLM::FFWP_ENCRYPTION_METHOD, DAAN_LICENSE_ENC_KEY, 0, FFWPLM_CYPHER);

        self::debug("Decrypted key: $decrypted_keys[$id].");

        /**
         * Run a quick validation, before returning the result.
         */
        if (self::validate($decrypted_keys[$id])) {
            self::debug("Validation succeeded for $id and key: $decrypted_keys[$id].");

            return $decrypted_keys[$id];
        }

        self::debug("Vaidation failed for $id and encrypted key: $key. Result was: $decrypted_keys[$id].");

        FFWPLM_Admin_Notice::set_notice(sprintf(__('Your Daan.dev license(s) failed to validate, likely because the encryption key has changed. Visit the <a href="%s">Manage Licenses</a> page and re-enter your license keys.', 'ffwp-license-manager'), admin_url('options-general.php?page=ffwp-license-manager')), 'error', 'ffwp-license-key-corrupt', 'all', 1);

        return '';
    }

    /**
     * Check if $key contains only letters and numbers and is 32 characters long.
     */
    public static function validate($key)
    {
        return preg_match('/^[A-Za-z0-9]{32}$/', $key) === 1;
    }

    /**
     * Encrypt key for storage.
     * 
     * @param mixed $key 
     * 
     * @return string 
     */
    public static function encrypt($key)
    {
        if (!defined(self::DAAN_LM_ENC_KEY_LABEL)) {
          //  FFWPLM_Admin_Notice::set_notice(sprintf(__('Your Daan.dev/Daan.dev license(s) failed to validate, because required encryption keys are missing. Visit the <a href="%s">Manage Licenses</a> page to fix it. If this message reappears, try reloading the page, otherwise <a href="%s">fix it manually</a>.', 'ffwp-license-manager'), admin_url('options-general.php?page=ffwp-license-manager'), 'https://daan.dev/docs/getting-started/activate-license/#encryption-key'), 'error', 'ffwp-encryption-key-missing', 'all', 1);

            return '';
        }

        return openssl_encrypt($key, FFWPLM::FFWP_ENCRYPTION_METHOD, DAAN_LICENSE_ENC_KEY, 0, FFWPLM_CYPHER);
    }

    /**
     * Initiate Daan.dev License Manager
     */
    private function init()
    {
        $db = get_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_DB_VERSION);

        if (version_compare($db, FFWPRESS_LICENSE_MANAGER_DB_VERSION) < 0) {
            $this->do_db_migration();
        }

        $this->do_admin();
        $this->do_ajax();
    }

    /**
     * @return FFWPLM_DB_Migration 
     */
    private function do_db_migration()
    {
        return new FFWPLM_DB_Migration();
    }

    /**
     * @return FFWPLM_Admin
     */
    private function do_admin()
    {
        return new FFWPLM_Admin();
    }

    /**
     * @return FFWPLM_Admin_AJAX 
     */
    private function do_ajax()
    {
        return new FFWPLM_Admin_AJAX;
    }

    /**
     * Install the encryption key to wp-config.php
     * 
     * @return void 
     * 
     * @throws Exception 
     */
    public static function install_encryption_key()
    {
        /*if (defined(self::DAAN_LM_ENC_KEY_LABEL)) {
            return;
        }

        if (file_exists(ABSPATH . 'wp-config.php') && is_writable(ABSPATH . 'wp-config.php')) {
            self::write_to_config_file(ABSPATH . 'wp-config.php');
        } elseif (file_exists(dirname(ABSPATH) . '/wp-config.php') && is_writable(dirname(ABSPATH) . '/wp-config.php')) {
            self::write_to_config_file(dirname(ABSPATH) . '/wp-config.php');
        }*/
    }

    /**
     * Generates a cypher of 64 characters and writes it to wp-config.php.
     * 
     * @since v1.10.2
     *        @var string $path The absolute path to to the wp-config.php file. 
     * 
     * @return void
     */
    private static function write_to_config_file($path)
    {
       /* $wp_config = file_get_contents($path);
        $label     = FFWPLM::DAAN_LM_ENC_KEY_LABEL;
        $enc_key   = defined('AUTH_SALT') ? AUTH_SALT : bin2hex(random_bytes(32));
        $wp_config = preg_replace("/^([\r\n\t ]*)(\<\?)(php)?/i", "<?php\n// Added by Daan.dev License Manager. Do not change/remove this.\ndefine('$label', '$enc_key');", $wp_config);

        file_put_contents($path, $wp_config);
    */
    
    }

    /**
     * Global debug logging function.
     * 
     * @param mixed $message 
     * @return void 
     */
    public static function debug($message)
    {
        if (!defined('FFWPLM_DEBUG_MODE') || FFWPLM_DEBUG_MODE === false) {
            return;
        }

        error_log(current_time('Y-m-d H:i:s') . ": $message\n", 3, trailingslashit(WP_CONTENT_DIR) . 'ffwplm-debug.log');
    }
}
