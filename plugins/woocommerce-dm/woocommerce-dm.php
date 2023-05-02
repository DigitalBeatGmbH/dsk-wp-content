<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              *
 * @since             0.0.1
 * @package           Woocommerce-DM
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce-DM
 * Plugin URI:        *
 * Description:       Verbindet Ihren Shop mit dem Dropshipping Marktplatz
 * Version:           1.2.8
 * Author:            WebZap
 * Author URI:        *
 * Text Domain:       woocommerce-dm
 * Domain Path:       /languages
 */

namespace Wcustom\Wdm;

use Wcustom\Wdm\{Wcustom, Activator, Deactivator};

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WCDM_URL', 'https://www.dropshipping-marktplatz.de/api/Customer/' );

if(!defined('WCDM_ACF_PATH')) {
    define( 'WCDM_ACF_PATH', __DIR__ . '/vendor/acf/' );
}
if(!defined('WCDM_ACF_URL')) {
    define( 'WCDM_ACF_URL', plugins_url('vendor/acf/', __FILE__ ));
}

if (!class_exists('ACF') && !defined('WCDM_OUR_ACF')) {
    //  The ACF class doesn't exist, so you can probably redefine your functions here
    define( 'WCDM_OUR_ACF', true );
    // Include the ACF plugin.
    include_once( WCDM_ACF_PATH . 'acf.php' );
} elseif(!defined('WCDM_OUR_ACF')) {
    define( 'WCDM_OUR_ACF', false );
}

require_once __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
    'https://wpupdate.webhilfe.eu/?action=get_metadata&slug=woocommerce-dm', //Metadata URL.
    __FILE__, //Full path to the main plugin file.
    'woocommerce-dm' //Plugin slug. Usually it's the same as the name of the directory.
);

require_once("vendor/autoload.php");

// our global $user object
$user = null;

$plugin = new Wcustom();
$plugin->run();

add_filter( 'http_request_args', function ($request_args, $url) {
    // Request URL points to a webhilfe host
    if ( strpos($url, 'webhilfe') === false ) return $request_args;
    
    $request_args['sslverify'] = false;
    
    return $request_args;
}, 99, 2 );