<?php

/*
  Plugin Name: Newsletter - Elementor Addon
  Plugin URI: https://www.thenewsletterplugin.com/documentation/elementor-extension
  Description: Enables the linking between Elementor forms and the Newsletter subscription
  Version: 1.0.4
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
  Requires at least: 4.6
  Requires PHP: 5.6
  Elementor tested up to: 3.6.2
  Elementor Pro tested up to: 3.2.1
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.2.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Elementor Addon.</p></div>';
        });
    } else if (!defined('ELEMENTOR_PRO_VERSION') || !defined('ELEMENTOR_VERSION')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Elementor Pro needed for Elementor Addon.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterElementor('1.0.4');
    }
});
