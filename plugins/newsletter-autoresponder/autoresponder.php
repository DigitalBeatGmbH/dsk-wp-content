<?php

/*
  Plugin Name: Newsletter - Autoresponder
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/autoresponder-extension/
  Description: Build email series for your customers and keep them engaged
  Version: 1.3.8
  Requires PHP: 5.6
  Requires at least: 4.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.3.1') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Autoresponder Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterAutoresponder('1.3.8');
    }
});

register_activation_hook(__FILE__, function () {
    update_option('newsletter_autoresponder_version', '', false);
});

register_deactivation_hook(__FILE__, function () {
    delete_transient('newsletter_autoresponder_run');
});
