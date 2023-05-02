<?php

/*
  Plugin Name: Newsletter - Reports and Retargeting
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/reports-extension/
  Description: Extends the statistic viewer adding graphs, link clicks, export and many other data. Automatic updates available setting the license key on Newsletter configuration panel.
  Version: 4.4.4
  Requires at least: 4.6
  Requires PHP: 5.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Reports Addon.</p></div>';
        });
    } else {
        include __DIR__ . '/plugin.php';
        new NewsletterReports('4.4.4');
    }
});
