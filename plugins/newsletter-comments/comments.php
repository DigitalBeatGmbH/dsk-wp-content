<?php

/*
  Plugin Name: Newsletter - Subscribe on Comments
  Plugin URI: https://www.thenewsletterplugin.com
  Description: Add the subscription option to your blog comment form
  Version: 1.0.9
  Requires PHP: 5.6
  Requires at least: 4.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */
add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for WP Comments Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterComments('1.0.9');
    }
});

