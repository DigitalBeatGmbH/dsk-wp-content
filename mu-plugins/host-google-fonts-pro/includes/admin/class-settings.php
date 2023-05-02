<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 * @copyright © 2022 Daan van den Bergh. All Rights Reserved.
 */
class OmgfPro_Admin_Settings
{
	/**
	 * Internal Use
	 */
	const OMGF_PRO_DB_VERSION            = 'omgf_pro_db_version';
	const OMGF_PRO_PROCESSED_STYLESHEETS = 'omgf_pro_processed_local_stylesheets';

	/**
	 * Settings Page
	 */
	const OMGF_PRO_ADMIN_PAGE = 'optimize-webfonts';

	/**
	 * Settings Fields
	 */
	const OMGF_PRO_SETTINGS_FIELD_OPTIMIZE  = 'omgf-optimize-settings';
	const OMGF_PRO_SETTINGS_FIELD_DETECTION = 'omgf-detection-settings';
	const OMGF_PRO_SETTINGS_FIELD_ADVANCED  = 'omgf-advanced-settings';

	/**
	 * Optimize Fonts
	 */
	const OMGF_OPTIMIZE_SETTING_FORCE_FONT_DISPLAY  = 'omgf_pro_force_font_display';
	const OMGF_OPTIMIZE_SETTING_REMOVE_ASYNC_FONTS  = 'omgf_pro_remove_async_fonts';
	const OMGF_OPTIMIZE_SETTING_REPLACE_FONT		= 'omgf_pro_replace_font';
	const OMGF_OPTIMIZE_SETTING_FALLBACK_FONT_STACK = 'omgf_pro_fallback_font_stack';

	/**
	 * Detection Settings
	 */
	const OMGF_DETECTION_SETTING_PROCESS_LOCAL_STYLESHEETS = 'omgf_pro_process_local_stylesheets';
	const OMGF_DETECTION_SETTING_PROCESS_INLINE_STYLES     = 'omgf_pro_process_inline_styles';
	const OMGF_DETECTION_SETTING_PROCESS_WEBFONT_LOADER    = 'omgf_pro_process_webfont_loader';
	const OMGF_DETECTION_SETTING_PROCESS_EARLY_ACCESS      = 'omgf_pro_process_early_access';

	/**
	 * Advanced Settings
	 */
	const OMGF_ADV_SETTING_EXCLUDED_IDS = 'omgf_pro_excluded_ids';
	const OMGF_ADV_SETTING_SOURCE_URL   = 'omgf_pro_source_url';

	/** @var string $active_tab */
	private $active_tab;

	/** @var string $page */
	private $page;

	/**
	 * OmgfPro_Admin_Settings constructor.
	 */
	public function __construct()
	{
		$this->active_tab = $_GET['tab'] ?? 'omgf-optimize-settings';
		$this->page       = $_GET['page'] ?? '';

		$this->init();
	}

	/**
	 * Initialize hooks
	 * 
	 * @return void 
	 */
	private function init()
	{
		add_filter('omgf_settings_constants', [$this, 'add_constants'], 10, 1);
	}

	/**
	 * @param $constants
	 *
	 * @return array
	 * @throws ReflectionException
	 */
	public function add_constants($constants)
	{
		if (
			$this->active_tab !== self::OMGF_PRO_SETTINGS_FIELD_OPTIMIZE
			&& $this->active_tab !== self::OMGF_PRO_SETTINGS_FIELD_DETECTION
			&& $this->active_tab !== self::OMGF_PRO_SETTINGS_FIELD_ADVANCED
		) {
			return $constants;
		}

		$reflection    = new ReflectionClass($this);
		$new_constants = $reflection->getConstants();

		return array_merge($new_constants, $constants);
	}
}
