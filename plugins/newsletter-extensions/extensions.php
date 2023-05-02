<?php

/*
  Plugin Name: Newsletter - Addons Manager
  xPlugin URI: http://www.thenewsletterplugin.com/documentation/extensions-extension
  Description: Manages all premium and free Newsletter addons directly from your blog
  Version: 1.1.8
  Requires at least: 4.6
  Requires PHP: 5.6
  Author: The Newsletter Team
  Author URI: http://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.3.5') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Addons Manager.</p></div>';
        });
    } else {
        include __DIR__ . '/plugin.php';
        new NewsletterExtensions('1.1.8');
    }
});
