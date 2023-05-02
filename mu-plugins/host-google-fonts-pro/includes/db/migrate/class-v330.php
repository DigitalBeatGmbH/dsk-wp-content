<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2022 Daan van den Bergh. All Rights Reserved.
 * @since     v3.3.0
 */
class OmgfPro_DB_Migrate_V330
{
    const DB_ENTRIES_TO_REMOVE = [
        'omgf_cache_is_stale',
        'omgf_cache_keys',
        'omgf_optimized_fonts',
        'omgf_unload_fonts',
        'omgf_preload_fonts',
        'omgf_unload_stylesheets'
    ];

    const DB_ENTRIES_TO_MIGRATE = [
        'omgf_pro_process_stylesheet_imports',
        'omgf_pro_process_stylesheet_font_faces'
    ];

    /** @var $current_version The version that initially triggered the migration scripts. */
    private $current_version = '';

    /** @var string $plugin_text_domain */
    private $plugin_text_domain = 'omgf-pro';

    /** @var $version string The version number this migration script was introduced with. */
    private $version = '3.3.0';

    /**
     * Buid
     * 
     * @return void 
     */
    public function __construct($current_version)
    {
        $this->current_version = $current_version;

        $this->init();
    }

    /**
     * Initialize
     * 
     * @return void 
     */
    private function init()
    {
        // Don't run this migration script if it's a fresh install.
        if ($this->current_version == false) {
            return;
        }

        /**
         * Remove previously saved default value of Fonts Source URL.
         */
        $fonts_source_url = get_option(OmgfPro_Admin_Settings::OMGF_ADV_SETTING_SOURCE_URL);

        /**
         * @since v3.4.3 Using content_url here is warranted, because we need this specific value.
         */
        if ($fonts_source_url == content_url('/uploads/localfont')) {
            delete_option(OmgfPro_Admin_Settings::OMGF_ADV_SETTING_SOURCE_URL);
        }

        /**
         * Check if OMGF is active, to prevent fatal errors
         * 
         * @since v3.5.0
         */
        if (defined('OMGF_UPLOAD_DIR')) {
            /**
             * Flush cached files and relevant db entries.
             */
            $dirs = array_filter((array) glob(OMGF_UPLOAD_DIR . '/*'));

            foreach ($dirs as $dir) {
                $this->delete($dir);
            }
        }

        foreach (self::DB_ENTRIES_TO_REMOVE as $option) {
            delete_option($option);
        }

        OmgfPro_Admin_Notice::set_notice(sprintf(__('Your OMGF Pro cache needs to be refreshed and has been flushed. Please <a href="%s">review your settings</a> and re-configure your stylesheets where needed.', $this->plugin_text_domain), admin_url('options-general.php?page=optimize-webfonts')));

        /**
         * Migrate local stylesheets settings to new option.
         */
        foreach (self::DB_ENTRIES_TO_MIGRATE as $option_name) {
            $option = get_option($option_name);

            if ($option != false) {
                update_option(OmgfPro_Admin_Settings::OMGF_DETECTION_SETTING_PROCESS_LOCAL_STYLESHEETS, 'on');

                break;
            }

            delete_option($option_name);
        }

        /**
         * Update stored version number.
         */
        update_option(OmgfPro_Admin_Settings::OMGF_PRO_DB_VERSION, $this->version);
    }

    /**
     * @param mixed $entry 
     * 
     * @return void 
     */
    private function delete($entry)
    {
        if (is_dir($entry)) {
            $file = new \FilesystemIterator($entry);

            // If dir is empty, valid() returns false.
            while ($file->valid()) {
                $this->delete($file->getPathName());
                $file->next();
            }

            rmdir($entry);
        } else {
            unlink($entry);
        }
    }
}
