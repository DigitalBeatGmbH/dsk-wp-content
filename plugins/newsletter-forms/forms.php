<?php
namespace TNP\Forms;
/*
  Plugin Name: Newsletter - Forms
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/bounce-extension/
  Description: Design your own subscription forms 
  Version: 1.0.5
  Requires PHP: 5.6
  Requires at least: 5.0.0
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.6.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Forms Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new Addon('1.0.5');
    }
});
