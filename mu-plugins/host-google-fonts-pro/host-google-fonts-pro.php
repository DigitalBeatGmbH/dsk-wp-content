<?php
defined('ABSPATH') || exit;
/**
 * Plugin Name: OMGF Pro
 * Plugin URI: 
 * Description: Premium add-on for OMGF. Requires OMGF to activate.
 * Version: 3.6.6
 * Author: Daan from Daan.dev
 * Author URI: 
 * Text Domain: omgf-pro
 */

define('OMGF_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OMGF_PRO_PLUGIN_FILE', __FILE__);

define('OMGF_PRO_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('OMGF_PRO_DB_VERSION', '3.6.0');
define('OMGF_PRO_STATIC_VERSION', '3.6.6');
/**
 * Takes care of loading classes on demand.
 *
 * @param $class
 *
 * @return mixed|void
 */
function omgf_pro_autoload($class)
{
    $path = explode('_', $class);

    if ($path[0] != 'OmgfPro') {
        return;
    }

    if (!class_exists('FFWP_Autoloader')) {
        require_once OMGF_PRO_PLUGIN_DIR . 'ffwp-autoload.php';
    }

    $autoload = new FFWP_Autoloader($class);

    return include OMGF_PRO_PLUGIN_DIR . 'includes/' . $autoload->load();
}

spl_autoload_register('omgf_pro_autoload');

/**
 * @return OmgfPro
 */
function omgf_pro_init()
{
    static $omgf_pro = null;

    if ($omgf_pro === null) {
        $omgf_pro = new OmgfPro();
    }

    return $omgf_pro;
}

omgf_pro_init();
