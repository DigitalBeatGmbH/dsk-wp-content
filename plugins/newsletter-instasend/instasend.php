<?php

/*
  Plugin Name: Newsletter - Instasend (BETA)
  Plugin URI: https://www.thenewsletterplugin.com
  Description: Transform a blog post into a newsletter in seconds
  Version: 1.0.2
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

add_action('newsletter_loaded', function ($version) {
    if ($version < '6.6.5') {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Newsletter plugin upgrade required for Instasend.</p></div>';
        });
    } else {
        include_once __DIR__ . '/plugin.php';
        new NewsletterInstasend('1.0.2');
    }
});

