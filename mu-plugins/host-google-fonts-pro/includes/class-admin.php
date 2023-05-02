<?php

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *            
 * @copyright © 2022 Daan van den Bergh. All Rights Reserved.
 */

defined('ABSPATH') || exit;

class OmgfPro_Admin
{
	const ADMIN_JS_HANDLE            = 'omgf-pro-admin-js';
	const FFWP_BASE_URL              = '';
	const OMGF_PRO_SETTINGS_PAGE	 = 'options-general.php?page=optimize-webfonts';

	/** @var string $plugin_text_domain */
	private $plugin_text_domain = 'omgf-pro';

	/**
	 * OmgfPro_Admin constructor.
	 */
	public function __construct()
	{
		/** Admin-wide stuff. */
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

		/** Add options for which a cache flush notice should be shown. */
		add_filter('omgf_admin_stale_cache_options', [$this, 'add_show_notice_options'], 10, 1);

		/** Remove promotional material and modify page title */
		add_filter('apply_omgf_pro_promo', '__return_false');
		add_filter('omgf_pro_promo', '__return_empty_string');
		add_filter('omgf_settings_page_title', [$this, 'do_page_title']);
		add_filter('omgf_help_tab_plugin_url', [$this, 'rewrite_plugin_url']);

		/** Add registration link to this plugin's row in plugins screen */
		//add_filter('plugin_action_links_' . plugin_basename(OMGF_PRO_PLUGIN_FILE), [$this, 'registration_link']);

		/**  */
		add_filter('pre_update_option_omgf_pro_force_subsets', [$this, 'convert_selected_options'], 10, 2);
	}

	/**
	 * Enqueues the necessary JS and CSS and passes options as a JS object.
	 *
	 * @param $hook
	 */
	public function enqueue_admin_scripts($hook)
	{
		if ($hook == 'settings_page_optimize-webfonts') {
			wp_enqueue_script(self::ADMIN_JS_HANDLE, plugin_dir_url(OMGF_PRO_PLUGIN_FILE) . 'assets/js/omgf-pro-admin.js', ['jquery', 'omgf-admin-js'], OMGF_PRO_STATIC_VERSION, true);
		}
	}

	/**
	 * @param $options
	 *
	 * @return array
	 */
	public function add_show_notice_options($options)
	{
		$pro_options = [
			OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_LOCAL_STYLESHEETS,
			OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_INLINE_STYLES,
			OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_WEBFONT_LOADER,
			OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_EARLY_ACCESS,
			OmgfPro_Admin_Settings::OMGF_ADV_SETTING_SOURCE_URL
		];

		return array_merge($pro_options, $options);
	}

	/**
	 * @return string
	 */
	public function do_page_title()
	{
		return __('OMGF Pro', $this->plugin_text_domain);
	}

	/**
	 * @return string 
	 */
	public function rewrite_plugin_url()
	{
		return '';
	}

	/**
	 * @param $links
	 *
	 * @return string
	 */
	public function registration_link($links)
	{
		$admin_url     = admin_url() . 'options-general.php?page=ffwp-license-manager';
		$license_link  = "<a href='$admin_url'>" . __('Manage License', $this->plugin_text_domain) . "</a>";
		$admin_url     = admin_url('options-general.php?page=optimize-webfonts');
		$settings_link = "<a href='$admin_url'>" . __('Settings', $this->plugin_text_domain) . '</a>';
		array_push($links, $settings_link, $license_link);

		return $links;
	}

	/**
	 * @param $new_options
	 * @param $old_options
	 *
	 * @return false|string
	 */
	public function convert_selected_options($new_options, $old_options)
	{
		$new_options = wp_json_encode($new_options);

		if ($new_options == $old_options) {
			return $old_options;
		}

		return $new_options;
	}
}
