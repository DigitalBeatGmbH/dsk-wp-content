<?php
/*
  Plugin Name: Newsletter - Leads
  Plugin URI: https://www.thenewsletterplugin.com/documentation/addons/extended-features/leads-extension/
  Description: Adds a leads generation system to the Newsletter plugin. Automatic updates available setting the license key on Newsletter configuration panel.
  Version: 1.2.8
  Requires at least: 4.6
  Requires PHP: 5.6
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.4.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required by Leads Addon for Newsletter.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterLeads('1.2.8');
    }
});