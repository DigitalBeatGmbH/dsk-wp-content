<?php

/*
  Plugin Name: Newsletter - WP Users Integration
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/wpusers-extension/
  Description: Integrates the WP user registration with Newsletter subscription
  Text Domain: newsletter-wpusers
  Domain Path: /languages
  Version: 1.2.9
  Requires PHP: 5.6
  Requires at least: 4.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for WP User Registration Integration.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterWpUsers('1.2.9');
    }
});

register_activation_hook(__FILE__, function () {
    update_option('newsletter_wpusers_version', '', false);
});
