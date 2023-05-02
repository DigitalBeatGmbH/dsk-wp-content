<?php

/*
  Plugin Name: Newsletter - Webhooks
  Plugin URI: http://www.thenewsletterplugin.com
  Description: Adds webhook capabilities to connect to external systems
  Version: 1.0.6
  Author: The Newsletter Team
  Author URI: http://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
  Requires at least: 4.6
  Requires PHP: 5.6
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.3.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Webhooks Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterWebhooks('1.0.6');
    }
});

