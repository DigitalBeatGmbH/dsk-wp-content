<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 * @copyright © 2022 Daan van den Bergh. All Rights Reserved.
 * @since     v2.4.0
 */
class OmgfPro_DB_Migrate_V240
{
    /** Options to be migrated. */
    const OMGF_MIGRATE_OPTION_CDN_URL      = 'omgf_cdn_url';
    const OMGF_MIGRATE_OPTION_REL_URLS     = 'omgf_relative_url';
    const OMGF_MIGRATE_OPTION_ALT_REL_PATH = 'omgf_cache_uri';
    const OMGF_MIGRATE_OPTION_WOFF2_ONLY   = 'omgf_woff2_only';

    /** @var $version string The version number this migration script was introduced with. */
    private $version = '2.4.0';

    /**
     * Buid
     * 
     * @return void 
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize
     * 
     * @return void 
     */
    private function init()
    {
        $this->migrate_src_url_options();
        $this->migrate_woff2_only_option();
        $this->clean_up($this->get_option_names(__CLASS__));

        /**
         * Update stored version number.
         */
        update_option(OmgfPro_Admin_Settings::OMGF_PRO_DB_VERSION, $this->version);
    }

    /**
     * Migrate all relevant options to Fonts Source URL option. 
     * 
     * @return void 
     */
    private function migrate_src_url_options()
    {
        $src_url  = OMGF_PRO_SOURCE_URL;
        $rel_path = '/' . implode('/', array_slice(explode('/', $src_url), 3));

        // Replace regular relative path (Cache Path option) with Alternative Relative Path.
        if ($alt_rel_path = get_option(self::OMGF_MIGRATE_OPTION_ALT_REL_PATH)) {
            $src_url = str_replace($rel_path, $alt_rel_path, $src_url);
        }

        // Removes everything before the third '/', i.e. the Home URL.
        if (get_option(self::OMGF_MIGRATE_OPTION_REL_URLS)) {
            $src_url = $rel_path;

            if ($alt_rel_path) {
                $src_url = $alt_rel_path;
            }
        }

        // Replace Home URL with CDN URL if set.
        if ($cdn_url = get_option(self::OMGF_MIGRATE_OPTION_CDN_URL)) {
            $src_url = str_replace(home_url(), $cdn_url, $src_url);
        }

        update_option(OmgfPro_Admin_Settings::OMGF_ADV_SETTING_SOURCE_URL, $src_url);
    }

    /**
     * Migrate WOFF2 only option to Include File Types option if enabled.
     * 
     * @return void 
     */
    private function migrate_woff2_only_option()
    {
        if (get_option(self::OMGF_MIGRATE_OPTION_WOFF2_ONLY)) {
            update_option(OmgfPro_Admin_Settings::OMGF_OPTIMIZE_SETTING_FILE_TYPES, ['woff2']);
        }
    }

    /**
     * Clean up options in wp_option table.
     * 
     * @param array $options 
     * @return void 
     */
    private function clean_up(array $options)
    {
        foreach ($options as $option) {
            delete_option($option);
        }
    }

    /**
     * Get class constants.
     * 
     * @return array 
     */
    private function get_option_names($class_name)
    {
        $class = new ReflectionClass($class_name);

        return array_values($class->getConstants());
    }
}
