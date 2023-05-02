<?php

namespace Wcustom\Wdm;

use Wcustom\Wdm\{Loader, I18n, CustomFields};
use Wcustom\Wdm\Admin\Admin;
use Wcustom\Wdm\Dropshipping\{Dtimes, Units, Taxes, Log};

/**
 * The admin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      0.0.1
 * @package    Wcustom
 * @subpackage Wcustom/includes
 * @author     WebZap
 */
class Wcustom
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Wcustom_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct()
	{
	    global $user;

		$this->plugin_name = 'woocommerce-dm';
		$this->version = '1.2.8';

		if($this->load_dependencies()) {
    		$this->set_locale();
    		
    		$this->define_db_hooks();
    		
    		$this->loader->add_action( 'after_rocket_clean_domain', $this, 'clear_cache' );
    
    		if(is_admin() && defined('DOING_AJAX') && DOING_AJAX) { // ajax is always is_admin !!
    		    $this->define_ajax_hooks();
    		} elseif(is_admin()) {
                $this->define_admin_hooks();
    		} else {
    		    $this->define_public_hooks();
    		}
    
    		$this->define_cron_hooks();
    		
    		$this->define_api_hooks();
		}
	}
	
	/**
	 * resister our API Enpoints
	 */
	private function define_api_hooks()
	{
	    /*$api = new RestUser();
	    $this->loader->add_action( 'rest_api_init', $api, 'register_routes');*/
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wcustom_Loader. Orchestrates the hooks of the plugin.
	 * - Wcustom_i18n. Defines internationalization functionality.
	 * - Wcustom_Admin. Defines all hooks for the admin area.
	 * - Wcustom_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies()
	{
	    $this->loader = new Loader($this->version);
	    
	    if(WCDM_OUR_ACF) {
	        $this->loader->add_filter('acf/settings/url', $this->loader, 'acf_settings_url');
	        $this->loader->add_filter('acf/settings/show_admin', $this->loader, 'acf_settings_show_admin');
	    }
	    
	    if(
			( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && !array_key_exists('woocommerce/woocommerce.php', apply_filters('active_plugins', get_site_option('active_sitewide_plugins') ) ) ) ||
	        (!in_array( 'woocommerce-germanized/woocommerce-germanized.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && ! array_key_exists('woocommerce-germanized/woocommerce-germanized.php', apply_filters('active_plugins', get_site_option('active_sitewide_plugins') ) ) )
	        ) {
	            
            $this->set_locale();
            
    	    $plugin_admin = new Admin();
            $this->loader->add_action( 'plugins_loaded', $plugin_admin, 'plugins_not_activated', 100 );
    	    return false;
	    }
	    
	    $this->loader->add_filter('http_request_args', $this->loader, 'http_request_args', 10, 2);
	    add_filter( 'wc_product_has_unique_sku', '__return_false');
	    
	    // CUSTOM FIELDS
	    $custom_fields = new CustomFields($this->version);
	    $this->loader->add_action( 'acf/init', $custom_fields, 'register' );
	    
	    return true;
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wcustom_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale()
	{

	    $plugin_i18n = new I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks()
	{
	    global $user;
	    
	    // ADMIN HOOKS
	    $plugin_admin = new Admin($this->version);
	    
	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	    
	    $this->loader->add_action( 'init',                 $plugin_admin, 'add_plugin_options_admin_page', 8 );
	    $this->loader->add_action( 'admin_menu',           $plugin_admin, 'add_plugin_actions_admin_page', 105 );
	    
	    $this->loader->add_filter("woocommerce_order_actions", $plugin_admin, 'add_single_order_action_button', 100, 1);
	    $this->loader->add_filter( 'woocommerce_admin_order_actions', $plugin_admin, 'add_list_order_action_button', 100, 2 );
	    $this->loader->add_action( 'admin_head',           $plugin_admin, 'add_custom_order_status_actions_button_css' );
	    
	    $this->loader->add_action("woocommerce_order_action_woocommerce-dm-senddm", $plugin_admin, 'woocommerce_dm_senddm');
	    
	    $this->loader->add_action( 'admin_notices', $plugin_admin, 'display_flash_notices', 12 );
	    
	    $this->loader->add_action('acf/save_post', $plugin_admin, 'import_settings', 20);
	    
	    $plugin_tax = new Taxes($this->version);
	    $this->loader->add_action( 'admin_post_taxes_form_response', $plugin_tax, 'taxes_form_response');
	    
	    $plugin_log = new Log();
	    $this->loader->add_filter( 'manage_dm_log_posts_columns', $plugin_log, 'set_custom_columns' );
	    $this->loader->add_action( 'manage_dm_log_posts_custom_column', $plugin_log, 'custom_columns', 10, 2 );
	    $this->loader->add_action( 'manage_dm_log_posts_columns', $plugin_log, 'old_columns', 10, 1 );
	    
	    global $typenow;
	    
	    if (empty($typenow)) {
	        // try to pick it up from the query string
	        if (!empty($_GET['post'])) {
	            $post = get_post($_GET['post']);
	            $typenow = $post->post_type;
	        }
	        if (!empty($_GET['post_type'])) {
	            $typenow = $_GET['post_type'];
	        }
	    }
	    
	    if ($typenow == 'dm_log') {
    	    add_filter('display_post_states', '__return_false');
    	    
	        $this->loader->add_action('edit_form_after_title', $plugin_log, 'adminEditAfterTitle', 100);
	        $this->loader->add_filter('post_row_actions', $plugin_log, 'adminPostRowActions', 10, 2);
	        $this->loader->add_filter('bulk_actions-edit-log_emails_log', $plugin_log, 'adminBulkActionsEdit');
	        $this->loader->add_action('admin_print_footer_scripts', $plugin_log, 'adminPrintFooterScripts');
    	    
	        $this->loader->add_action('in_admin_header', $plugin_log, 'adminScreenLayout');
	        $this->loader->add_filter('views_edit-log_emails_log', $plugin_log, 'adminViewsEdit');
	    }
	}
	
	/**
	 * Register all of the hooks related to the ajax functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_ajax_hooks()
	{
	    global $user;
		
	    $plugin_admin = new Admin($this->version);
	    
	    $this->loader->add_action( 'wp_ajax_woocommerce-dm-check',        $plugin_admin, 'check' );
	    $this->loader->add_action( 'wp_ajax_nopriv_woocommerce-dm-check', $plugin_admin, 'check' );
	    
	    $this->loader->add_action( 'wp_ajax_woocommerce-dm-setflag',        $plugin_admin, 'set_flag' );
	    $this->loader->add_action( 'wp_ajax_nopriv_woocommerce-dm-setflag', $plugin_admin, 'set_flag' );
	    
	    $this->loader->add_action( 'wp_ajax_woocommerce-dm-import_all',        $plugin_admin, 'import_all' );
	    $this->loader->add_action( 'wp_ajax_nopriv_woocommerce-dm-import_all', $plugin_admin, 'import_all' );
	    
	    $this->loader->add_action( 'wp_ajax_woocommerce-dm-import_stock',        $plugin_admin, 'import_stock' );
	    $this->loader->add_action( 'wp_ajax_nopriv_woocommerce-dm-import_stock', $plugin_admin, 'import_stock' );
	    
	    $this->loader->add_action( 'wp_ajax_woocommerce-dm-senddm',        $plugin_admin, 'woocommerce_dm_senddm' );
	    $this->loader->add_action( 'wp_ajax_nopriv_woocommerce-dm-senddm', $plugin_admin, 'woocommerce_dm_senddm' );
	    
	    $plugin_dtimes = new Dtimes($this->version);
	    $this->loader->add_action( 'wp_ajax_woocommerce-dm-import_dtimes',        $plugin_dtimes, 'import_dtimes' );
	    $this->loader->add_action( 'wp_ajax_nopriv_woocommerce-dm-import_dtimes', $plugin_dtimes, 'import_dtimes' );
	    
	    $plugin_units = new Units($this->version);
	    $this->loader->add_action( 'wp_ajax_woocommerce-dm-import_units',        $plugin_units, 'import_units' );
	    $this->loader->add_action( 'wp_ajax_nopriv_woocommerce-dm-import_units', $plugin_units, 'import_units' );
	    
	    $plugin_tax = new Taxes($this->version);
	    $this->loader->add_action( 'wp_ajax_woocommerce-dm-import_taxes',        $plugin_tax, 'import_taxes' );
	    $this->loader->add_action( 'wp_ajax_nopriv_woocommerce-dm-import_taxes', $plugin_tax, 'import_taxes' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks()
	{   
	    global $user;
	    
	}
	
	/**
	 * Register all of the hooks related to the redirect functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	public function define_redirect_hooks()
	{
	    global $wp_query;
	}
	
	/**
	 * Register all of the hooks related to the user-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	public function define_user_hooks()
	{
	    global $user;
	    
	}
	
	/**
	 * Register all of the hooks related to DB manipulation
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_db_hooks()
	{
	    $plugin_activator = new Activator($this->version);
	    $this->loader->add_action( 'plugins_loaded', $plugin_activator, 'update_db_check' );
	}
	
	/**
	 * Register all of the hooks related to the cron functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_cron_hooks()
	{
	    $plugin_cron = new Cron($this->version);
	    
	    // ADD CUSTOM INTERVAL
	    $this->loader->add_filter( 'cron_schedules', $plugin_cron, 'add_intervals' );
        
		// CRON/SCHEDULE INIT
		$this->loader->add_action( 'init', $plugin_cron, 'cron_activation' );
	}
	
	/**
	 * start session handling
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	public function start_session()
	{
	    if(!session_id()) {
	        session_start();
	    }
	}
	
	
	/**
	 * end session handling
	 *
	 * @since    0.0.1
	 * @access   private
	 */
    public function end_session()
    {
        session_destroy();
    }
	
	/**
	 * WP Rocket Extra Cache Delete
	 */
	public function clear_cache()
	{
		// WCUSTOM Visitor Cache
		/*$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(WPMU_PLUGIN_DIR . '/wcustom/src/Visitor/cache'), \RecursiveIteratorIterator::SELF_FIRST);
		foreach($objects as $pathname => $fileinfo) {
			if (!$fileinfo->isFile()) continue;
			@unlink($pathname);
		}*/
	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Wcustom_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

}