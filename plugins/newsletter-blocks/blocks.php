<?php

/*
  Plugin Name: Newsletter - Extended Composer Blocks
  Plugin URI: https://www.thenewsletterplugin.com
  Description: New extended blocks for the composer
  Version: 1.3.8
  Author: The Newsletter Team
  Requires at least: 4.6
  Requires PHP: 5.6
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Extended Blocks.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterBlocks('1.3.8');
    }
});
