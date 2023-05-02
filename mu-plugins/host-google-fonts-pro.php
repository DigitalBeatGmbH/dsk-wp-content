<?php
/**
 * Plugin Name: OMGF Pro
 * Plugin URI: 
 * Description: Premium add-on for OMGF. Requires OMGF to activate.
 * Version: 3.6.6
 * Author: Daan from Daan.dev
 * Author URI: 
 * Text Domain: omgf-pro
 */
 
defined('ABSPATH') or die();
require WPMU_PLUGIN_DIR . '/host-google-fonts-pro/host-google-fonts-pro.php';

function mu_hide_plugins_network( $plugins ) {

    if( in_array( 'host-google-fonts-pro/host-google-fonts-pro.php', array_keys( $plugins ) ) ) {
        unset( $plugins['host-google-fonts-pro/host-google-fonts-pro.php'] );
    }

    return $plugins;
    
}

add_filter( 'all_plugins', 'mu_hide_plugins_network' );