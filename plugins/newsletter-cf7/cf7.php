<?php

/*
  Plugin Name: Newsletter - Contact Form 7
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/integrations/contact-form-7-extension/
  Description: Adds subscription option to Contact Form 7 forms
  Version: 4.2.6
  Requires PHP: 5.6
  Requires at least: 4.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Archive Addon.</p></div>';
        });
    } if (!defined('WPCF7_VERSION') || WPCF7_VERSION < '5.0.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Contact Form 7 version 5+ required for Newsletter plugin integration.</p></div>';
        });
    }else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterCF7('4.2.6');
    }
});

register_activation_hook(__FILE__, function () {
    update_option('newsletter_cf7_version', '', false);
});