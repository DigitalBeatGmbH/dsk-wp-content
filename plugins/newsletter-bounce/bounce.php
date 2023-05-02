<?php

/*
  Plugin Name: Newsletter - Bounce Management
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/bounce-extension/
  Description: Bounce detection for Newsletter
  Version: 1.1.9
  Requires PHP: 5.6
  Requires at least: 5.0.0
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Bounce Management Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterBounce('1.1.9');
    }
});

register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('newsletter_bounce_run');
});

//register_activation_hook(__FILE__, function () {
//    update_option('newsletter_bounce_version', '', false);
//});