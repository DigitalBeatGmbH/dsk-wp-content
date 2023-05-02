<?php

/*
  Plugin Name: Newsletter - Translatepress
  Plugin URI: https://www.thenewsletterplugin.com/
  Description: Adds support for translatepress
  Version: 1.0.0
  Author: The Newsletter Team
  Author URI: https://www.thenewsletterplugin.com/
 */

class NewsletterTranslatepress {

    var $prefix = 'newsletter_translatepress';
    var $slug = 'newsletter-translatepress';
    var $plugin = 'newsletter-translatepress/translatepress.php';
    var $id = 82;

    function __construct() {
        add_action('init', array($this, 'hook_init'));
    }

    public function hook_init() {
        if (!class_exists('Newsletter')) {
            return;
        }

        if (!class_exists('TRP_Translate_Press')) {
            return;
        }

        if (is_admin()) {
            add_action('admin_bar_menu', array($this, 'hook_admin_bar_menu'), 100);
        }

        add_filter('site_transient_update_plugins', array($this, 'hook_site_transient_update_plugins'));
        add_filter('newsletter_current_language', array($this, 'hook_newsletter_current_language'));
        add_filter('newsletter_languages', array($this, 'hook_newsletter_languages'));
    }

    function hook_site_transient_update_plugins($value) {
        if (!method_exists('Newsletter', 'set_extension_update_data')) {
            return $value;
        }

        return Newsletter::instance()->set_extension_update_data($value, $this);
    }

    function hook_newsletter_languages($languages) {

        // TODO: Add a cache

        $trp = TRP_Translate_Press::get_trp_instance();
        $trp_languages = $trp->get_component('languages');
        $trp_settings = $trp->get_component('settings');

        $settings = $trp_settings->get_settings();
        $langs = $trp_languages->get_language_names($settings['publish-languages']);

        foreach ($langs as $code => $name) {
            $code = substr($code, 0, 2);
            $languages[$code] = $name;
        }
        return $languages;
    }

    function hook_newsletter_current_language($language) {
        if (is_admin()) {
            return $this->get_current_language();
        } else {
            global $TRP_LANGUAGE;
            if (isset($TRP_LANGUAGE)) {
                // TODO: Manage even the country code?
                return substr($TRP_LANGUAGE, 0, 2);
            }
        }
        return $language;
    }

    function get_current_language() {
        global $current_user;

        $current_language = '';
        if (isset($_GET['lang'])) {
            $current_language = $_GET['lang'];
            update_user_meta($current_user->ID, 'newsletter_translatepress_language', $current_language);
        } else {
            $user_language = $current_user->newsletter_translatepress_language;
            if ($user_language)
                $current_language = $user_language;
        }
        return substr($current_language, 0, 2);
    }

    function hook_admin_bar_menu($wp_admin_bar) {

        $all_item = (object) array(
                    'code' => '',
                    'name' => 'All languages'
        );

        $selected = $all_item;

        $trp = TRP_Translate_Press::get_trp_instance();
        $trp_languages = $trp->get_component('languages');
        $trp_settings = $trp->get_component('settings');

        $settings = $trp_settings->get_settings();
        $languages = $trp_languages->get_language_names($settings['publish-languages']);

        $items = array($all_item);
        $current_language = $this->get_current_language();
        foreach ($languages as $code => $name) {
            $code = substr($code, 0, 2);
            $item = (object) array('code' => $code, 'name' => $name);
            $items[] = $item;
            if ($code == $current_language)
                $selected = $item;
        }

        $wp_admin_bar->add_menu(array(
            'id' => 'languages',
            'title' => $selected->name,
            'href' => esc_url(add_query_arg('lang', $selected->code, remove_query_arg('paged'))),
            'meta' => array('title' => ''),
        ));

        foreach ($items as $lang) {
            if ($selected->code === $lang->code) {
                continue;
            }

            $wp_admin_bar->add_menu(array(
                'parent' => 'languages',
                'id' => $lang->code,
                'title' => esc_html($lang->name),
                'href' => esc_url(add_query_arg('lang', $lang->code, remove_query_arg('paged'))),
                    //'meta'   => 'all' === $lang->slug ? array() : array( 'lang' => esc_attr( $lang->get_locale( 'display' ) ) ),
            ));
        }
    }

}

new NewsletterTranslatepress();
