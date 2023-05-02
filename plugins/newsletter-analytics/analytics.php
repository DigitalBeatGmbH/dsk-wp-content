<?php

/*
  Plugin Name: Newsletter - Google Analytics
  Plugin URI: https://www.thenewsletterplugin.com/extensions/analytics
  Description: Adds Google Analytics tracking to the newsletter links
  Version: 1.1.6
  Requires at least: 5.0.0
  Requires PHP: 5.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Google Analytics Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterAnalytics('1.1.6');
    }
});

register_activation_hook(__FILE__, function () {
    update_option('newsletter_analytics_version', '', false);
});
