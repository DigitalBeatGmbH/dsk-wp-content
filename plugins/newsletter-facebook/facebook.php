<?php

/*
  Plugin Name: Newsletter - Facebook
  Plugin URI: http://www.thenewsletterplugin.com/plugins/newsletter/facebook-module
  Description: Add the one click subscription using the Facebook connect. Automatic updates available setting the license key on Newsletter configuration panel.
  Version: 4.0.9
  Author: The Newsletter Team
  Author URI: http://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '6.2.1') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Geo.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterFacebook('4.0.9');
    }
});

register_activation_hook(__FILE__, function () {
    update_option('newsletter_facebook_version', '', false);
});
