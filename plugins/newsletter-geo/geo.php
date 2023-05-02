<?php

/*
  Plugin Name: Newsletter - Geolocation
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/geolocation-extension/
  Description: Adds gelocation targeting to your campatigns
  Version: 1.1.6
  Requires PHP: 5.6
  Requires at least: 4.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.3.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Geo.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterGeo('1.1.6');
    }
});

register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('newsletter_geo_run');
});

register_activation_hook(__FILE__, function () {
    update_option('newsletter_geo_version', '', false);
});