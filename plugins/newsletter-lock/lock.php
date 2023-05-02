<?php

/*
  Plugin Name: Newsletter - Locked Content
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/locked-content-extension/
  Description: Hide partially or totally posts content and requires a subscription to unlock them
  Version: 1.1.3
  Requires PHP: 5.6
  Requires at least: 3.4.0
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Locked Content Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterLock('1.1.3');
    }
});

