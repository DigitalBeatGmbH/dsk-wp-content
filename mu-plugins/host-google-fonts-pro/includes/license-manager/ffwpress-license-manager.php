<?php
defined('ABSPATH') || exit;

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 * @version   v1.11.0
 */
class FFWPressLicenseManager
{
    /**
     * Build Class
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Actions and hooks.
     */
    private function init()
    {
        /**
         * Define global constants
         */
        define('FFWPRESS_LICENSE_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('FFWPRESS_LICENSE_MANAGER_PLUGIN_FILE', __FILE__);
        define('FFWPRESS_LICENSE_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('FFWPRESS_LICENSE_MANAGER_STATIC_VERSION', '1.10.5');
        define('FFWPRESS_LICENSE_MANAGER_DB_VERSION', '1.10.4');

        /**
         * Register Autoloader
         */
        spl_autoload_register([$this, 'autoload']);

        $this->run();
    }

    /**
     * Takes care of loading classes on demand.
     *
     * @param $class
     *
     * @return mixed|void
     */
    public function autoload($class)
    {
        $path = explode('_', $class);

        if ($path[0] != 'FFWPLM') {
            return;
        }

        $autoload = new FFWP_Autoloader($class);

        return include FFWPRESS_LICENSE_MANAGER_PLUGIN_DIR . 'includes/' . $autoload->load();
    }

    /**
     * All systems go!
     * 
     * @return FFWPLM 
     */
    private function run()
    {
        static $wlm = null;

        if ($wlm === null) {
            $wlm = new FFWPLM();
        }

        return $wlm;
    }
}

new FFWPressLicenseManager();
