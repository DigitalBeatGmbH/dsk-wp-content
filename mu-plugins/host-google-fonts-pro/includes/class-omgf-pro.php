<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *            
 * @copyright © 2022 Daan van den Bergh
 * @license   BY-NC-ND-4.0
 *            http://creativecommons.org/licenses/by-nc-nd/4.0/
 */
class OmgfPro
{
	const WORDPRESS_ORG_SEARCH_QUERY  = 'plugin-install.php?s=%s&tab=search&type=term';

	/** @var bool $halt Halt execution if dependencies aren't installed and/or activated. */
	private $halt = false;

	/** @var string $plugin_text_domain */
	private $plugin_text_domain = 'omgf-pro';

	/**
	 * Build class.
	 */
	public function __construct()
	{
		$this->init();
	}

	/**
	 * Init hooks & filters.
	 */
	private function init()
	{
		$this->do_license_manager();
		$this->define_constants();

		if (version_compare(OMGF_PRO_STORED_DB_VERSION, OMGF_PRO_DB_VERSION) < 0) {
			add_action('plugins_loaded', [$this, 'do_migrate_db']);
		}

		add_filter('omgf_upload_dir', [$this, 'rewrite_upload_dir']);
		add_filter('omgf_upload_url', [$this, 'rewrite_upload_url']);
		add_filter('omgf_optimize_url', [$this, 'remove_excessive_spacing'], 9);
		add_filter('omgf_optimize_fonts_object', [$this, 'optimize_additional_css'], 10, 2);
		add_filter('omgf_generate_stylesheet_after', [$this, 'append_additional_css'], 10, 2);

		if (is_admin()) {
			/** Add license field to Daan.dev License Manager */
			add_filter('ffwp_license_manager_licenses', [$this, 'do_license_field'], 1, 1);

			/**
			 * Priorities are set to 8 and 9, because register_settings runs at default priority (10).
			 * 
			 * @see   OMGF_Admin_Settings::create_menu()
			 * @since v3.0.5
			 */
			add_action('_admin_menu', [$this, 'do_enrollment'], 8);
			add_action('_admin_menu', [$this, 'init_admin'], 9);
			add_action('admin_notices', [$this, 'print_notices']);

			$this->add_ajax_hooks();
		}

		if (!is_admin()) {
			add_action('plugins_loaded', [$this, 'do_frontend_optimize'], 49);
		}
	}

	/**
	 * Loads the license manager submodule
	 * 
	 * @return void 
	 */
	private function do_license_manager()
	{
		if (!class_exists('FFWPLM')) {
			require_once(OMGF_PRO_PLUGIN_DIR . 'includes/license-manager/ffwpress-license-manager.php');
		}
	}

	/**
	 * Define constants.
	 */
	public function define_constants()
	{
		define('OMGF_PRO_STORED_DB_VERSION', esc_attr(get_option(OmgfPro_Admin_Settings::OMGF_PRO_DB_VERSION)));
		define('OMGF_PRO_REMOVE_ASYNC_FONTS', esc_attr(get_option(OmgfPro_Admin_Settings::OMGF_OPTIMIZE_SETTING_REMOVE_ASYNC_FONTS)));
		define('OMGF_PRO_FORCE_FONT_DISPLAY', esc_attr(get_option(OmgfPro_Admin_Settings::OMGF_OPTIMIZE_SETTING_FORCE_FONT_DISPLAY)));
		define('OMGF_PRO_FALLBACK_FONT_STACK', get_option(OmgfPro_Admin_Settings::OMGF_OPTIMIZE_SETTING_FALLBACK_FONT_STACK) ?: []);
		define('OMGF_PRO_REPLACE_FONT', get_option(OmgfPro_Admin_Settings::OMGF_OPTIMIZE_SETTING_REPLACE_FONT) ?: []);
		//define('OMGF_PRO_PROCESS_LOCAL_STYLESHEETS', esc_attr(get_option(OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_LOCAL_STYLESHEETS)));
		//define('OMGF_PRO_PROCESS_INLINE_STYLES', esc_attr(get_option(OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_INLINE_STYLES)));
		//define('OMGF_PRO_PROCESS_WEBFONT_LOADER', esc_attr(get_option(OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_WEBFONT_LOADER)));
		//define('OMGF_PRO_PROCESS_EARLY_ACCESS', esc_attr(get_option(OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_EARLY_ACCESS)));
		define('OMGF_PRO_PROCESS_LOCAL_STYLESHEETS', 'on');
		define('OMGF_PRO_PROCESS_INLINE_STYLES', 'on');
		define('OMGF_PRO_PROCESS_WEBFONT_LOADER', 'on');
		define('OMGF_PRO_PROCESS_EARLY_ACCESS', 'on');
		
		define('OMGF_PRO_SOURCE_URL', esc_attr(get_option(OmgfPro_Admin_Settings::OMGF_ADV_SETTING_SOURCE_URL)));
	}

	/**
	 * Run any DB migration scripts if needed.
	 * 
	 * @return void 
	 */
	public function do_migrate_db()
	{
		new OmgfPro_DB_Migrate();
	}

	/**
	 * @since v3.4.0 Use native wp_upload_dir() to add Multisite support.
	 * 
	 * @param string $dir 
	 * 
	 * @return string 
	 */
	public function rewrite_upload_dir()
	{
		return wp_upload_dir()['basedir'] . '/localfont';
	}

	/**
	 * @since v3.4.0 Use native wp_upload_dir() to add Multisite support.
	 * 
	 * @param string $dir
	 *  
	 * @return string 
	 */
	public function rewrite_upload_url()
	{
		if (OMGF_PRO_SOURCE_URL == '') {
			return wp_upload_dir()['baseurl'] . '/localfont';
		}

		return OMGF_PRO_SOURCE_URL;
	}

	/**
	 * Removes excessive spacing in $url.
	 * 
	 * @param string $url 
	 * @return string
	 */
	public function remove_excessive_spacing($url)
	{
		$url = preg_replace('/\s{2,}/', ' ', urldecode($url));
		$url = str_replace(', ', ',', $url);

		return $url;
	}

	/**
	 * @since v3.6.5 Processes any additional CSS classes found in stylesheets delivered by the Variable Fonts (CSS2) API.
	 * 
	 * @param object $fonts 
	 * @param string $url 
	 * 
	 * @return object 
	 */
	public function optimize_additional_css($fonts, $url)
	{
		$optimize = new OmgfPro_Optimize();

		return $optimize->additional_css($fonts, $url);
	}

	/**
	 * @since v3.6.5 Appends any additional CSS classes found in $fonts to $stylesheet.
	 * 
	 * @param string $stylesheet 
	 * @param object $fonts
	 *  
	 * @return string A valid CSS stylesheet.
	 */
	public function append_additional_css($stylesheet, $fonts)
	{
		$stylesheet_generator = new OmgfPro_StylesheetGenerator();

		return $stylesheet_generator->append_additional_css($stylesheet, $fonts);
	}

	/**
	 * @param $licenses
	 *
	 * @return array
	 */
	public function do_license_field($licenses)
	{
		$licenses[] = [
			'id'          => 4027,
			'label'       => __('OMGF Pro', $this->plugin_text_domain),
			'plugin_file' => OMGF_PRO_PLUGIN_FILE
		];

		return $licenses;
	}

	/**
	 * Initialize all Admin related tasks.
	 * 
	 * @return void 
	 */
	public function init_admin()
	{
		if ($this->halt) {
			return;
		}

		$this->do_settings();
		$this->do_admin();
	}

	/**
	 * Modify instructions for admin commands.
	 * 
	 * @return void 
	 */
	private function do_settings()
	{
		new OmgfPro_Admin_Settings();
	}

	/**
	 * Activates Pro options in OMGF's settings screen.
	 * 
	 * @return void 
	 */
	private function do_admin()
	{
		new OmgfPro_Admin();
	}

	/**
	 * Add notice to admin screen.
	 */
	public function print_notices()
	{
		OmgfPro_Admin_Notice::print_notice();
	}

	/**
	 * Modify behavior of OMGF's AJAX hooks.
	 * 
	 * @return void 
	 */
	private function add_ajax_hooks()
	{
		new OmgfPro_Ajax();
	}

	/**
	 * Run frontend Optimization logic.
	 */
	public function do_frontend_optimize()
	{
		if ($this->halt) {
			return;
		}

		new OmgfPro_Frontend_Optimize();
	}

	/**
	 * OMGF Pro depends on License Manager and OMGF to function properly.
	 * 
	 * @since v3.0.1 Changed required_plugins to static array. Let's count on it that people don't rename the folder on purpose.
	 *
	 * @return bool
	 */
	public function do_enrollment()
	{
		$required_plugins = [
			'OMGF' => defined('OMGF_PLUGIN_FILE') ? OMGF_PLUGIN_FILE : false,
		];
		$inactive_plugin = array_search(false, $required_plugins);
		$plugin_name     = get_plugin_data(OMGF_PRO_PLUGIN_FILE)['Name'];

		if ($inactive_plugin) {
			// Clear all previously set notices.
			delete_transient(OmgfPro_Admin_Notice::OMGF_PRO_ADMIN_NOTICE_TRANSIENT);
			
			OmgfPro_Admin_Notice::set_pro_notice(sprintf(__('<strong>%s</strong> needs to be installed and active for %s to function properly. Download it from <a href="%s"><em>Plugins > Add New</em></a> and make sure it\'s activated, before activating %s.', $this->plugin_text_domain), $inactive_plugin, $plugin_name, sprintf(admin_url(self::WORDPRESS_ORG_SEARCH_QUERY), $inactive_plugin), $plugin_name), 'error', 'omgf_pro_license_manager_not_active');

			deactivate_plugins(OMGF_PRO_PLUGIN_BASENAME);

			$this->halt = true;
		} elseif (get_option(OmgfPro_Admin_Notice::OMGF_PRO_ENROLLMENT_TRANSIENT) != true) {
			
			OmgfPro_Admin_Notice::set_pro_notice(
				sprintf(
					'<strong>' . __('Thank you for purchasing OMGF Pro! Head on over to the <a href="%s">settings screen</a> to take advantage of all the new, fancy features!', $this->plugin_text_domain) . '</strong>',
					admin_url(OmgfPro_Admin::OMGF_PRO_SETTINGS_PAGE)
				),
				'success',
				'omgf-pro-enrollment-notice'
			);

			update_option(OmgfPro_Admin_Notice::OMGF_PRO_ENROLLMENT_TRANSIENT, true);
		}

		/**
		 * Deactivate legacy FFW.Press License Manager, if it's still active. Throw a notice to inst
		 */
		$legacy_license_manager_active = defined('FFWP_LICENSE_MANAGER_PLUGIN_FILE') ? is_plugin_active(plugin_basename(FFWP_LICENSE_MANAGER_PLUGIN_FILE)) : false;

		if ($legacy_license_manager_active) {
			$license_manager_name = get_plugin_data(FFWP_LICENSE_MANAGER_PLUGIN_FILE)['Name'];

			OmgfPro_Admin_Notice::set_notice('<strong>' . sprintf(__('%s has been deactivated. You can safely delete that plugin, since it now comes packaged with %s.', $this->plugin_text_domain), $license_manager_name, $plugin_name) . '</strong>');

			deactivate_plugins(plugin_basename(FFWP_LICENSE_MANAGER_PLUGIN_FILE));
		}
	}
}
