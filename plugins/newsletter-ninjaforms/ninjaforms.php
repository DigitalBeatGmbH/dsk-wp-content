<?php

/*
  Plugin Name: Newsletter - Ninja Forms Addon
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/integrations/ninjaforms-extension/
  Description:
  Version: 1.0.8
  Requires at least: 4.6
  Requires PHP: 5.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Ninja Forms Addon.</p></div>';
        });
    } else if (!function_exists('Ninja_Forms')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Ninja Forms plugin required for Ninja Forms Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterNinjaForms('1.0.8');
    }
});

//register_activation_hook(__FILE__, function () {
//    update_option('newsletter_ninjaforms_version', '', false);
//});
