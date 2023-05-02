<?php

/*
  Plugin Name: Newsletter - WP Forms Addon
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/integrations/wpforms-extension/
  Description:
  Version: 1.1.2
  Requires at least: 5.0.0
  Requires PHP: 5.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for WP Forms Addon.</p></div>';
        });
    } else if (!class_exists('WPForms_Provider') || !class_exists('WPForms')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>WP Forms seems not installed.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterWPForms('1.1.2');
    }
});

