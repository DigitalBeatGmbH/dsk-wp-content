<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2022 Daan van den Bergh
 * @license   BY-NC-ND-4.0
 *            http://creativecommons.org/licenses/by-nc-nd/4.0/
 */
class OmgfPro_Ajax
{
    /**
     * Build class.
     * 
     * @return void 
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Init hooks and filters.
     * 
     * @return void 
     */
    private function init()
    {
        add_filter('omgf_clean_up_instructions', [$this, 'set_clean_up']);
    }

    /**
     * Add Fallback Font Stacks to db clean up before emptying cache directory.
     * 
     * @since v2.5.0
     *
     * @param mixed $instructions 
     * 
     * @return array containing a 'init', 'exclude' and 'queue'. 
     */
    public function set_clean_up($instructions)
    {
        $active_tab = $_GET['tab'] ?? 'omgf-optimize-settings';

        if ($active_tab !== OmgfPro_Admin_Settings::OMGF_PRO_SETTINGS_FIELD_OPTIMIZE) {
            return $instructions;
        }

        $section = $instructions['init'] ?? 'optimize-webfonts';

        if ($section == 'optimize-webfonts') {
            array_push($instructions['queue'], OmgfPro_Admin_Settings::OMGF_OPTIMIZE_SETTING_FALLBACK_FONT_STACK, OmgfPro_Admin_Settings::OMGF_PRO_PROCESSED_STYLESHEETS, OmgfPro_Admin_Settings::OMGF_OPTIMIZE_SETTING_REPLACE_FONT);
        }

        return $instructions;
    }
}
