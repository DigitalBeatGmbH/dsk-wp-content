<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2022 Daan van den Bergh. All Rights Reserved.
 * @since     v3.6.0
 */
class OmgfPro_DB_Migrate_V360
{
    const DB_ENTRIES_TO_MIGRATE = [
        'omgf_pro_force_subsets' => 'omgf_subsets'
    ];

    /** @var $current_version The version that initially triggered the migration scripts. */
    private $current_version = '';

    /** @var $version string The version number this migration script was introduced with. */
    private $version = '3.6.0';

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
         * Migrate local stylesheets settings to new option.
         */
        foreach (self::DB_ENTRIES_TO_MIGRATE as $old_name => $new_name) {
            // Don't set a default, because json_decode() can't decode arrays.
            $option = json_decode(get_option($old_name));

            if (!$option || empty($option)) {
                $option = ['latin', 'latin-ext'];
            }

            update_option($new_name, $option);

            delete_option($old_name);
        }

        /**
         * Update stored version number.
         */
        update_option(OmgfPro_Admin_Settings::OMGF_PRO_DB_VERSION, $this->version);
    }
}
