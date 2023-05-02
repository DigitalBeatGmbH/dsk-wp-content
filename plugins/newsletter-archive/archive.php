<?php

/*
  Plugin Name: Newsletter - Archive
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/archive-extension/
  Description: Enables a special short code which can be used in a WordPress page to show the sent newsletter archives.
  Version: 4.0.7
  Requires PHP: 5.6
  Requires at least: 4.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.3.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Archive Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterArchive('4.0.7');
    }
});
