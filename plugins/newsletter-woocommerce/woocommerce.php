<?php

/*
 * Plugin Name: Newsletter - WooCommerce
 * Plugin URI: https://www.thenewsletterplugin.com/woocommerce
 * Text Domain: newsletter-woocommerce
 * Domain Path: /languages
 * Description: Integrates Newsletter with WooCommerce. Automatic updates available with the license key.
 * Version: 1.7.5
 * Requires PHP: 5.6
 * Requires at least: 4.6
 * 
 * Author: The Newsletter Team
 * Author URI: https://www.thenewsletterplugin.com/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 6.3.1
 *
 * Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '7.3.0') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for WooCommerce addon.</p></div>';
        });
    } if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter Woocommerce Addon needs Woocommerce 3.0+.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterWoocommerce('1.7.5');
    }
});

register_activation_hook(__FILE__, function () {
    update_option('newsletter_woocommerce_version', '', false);
});
