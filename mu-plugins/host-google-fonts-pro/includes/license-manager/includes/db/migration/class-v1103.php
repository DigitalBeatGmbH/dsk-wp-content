<?php
defined('ABSPATH') || exit;

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */
class FFWPLM_DB_Migration_V1103
{
    private $version = '1.10.3';

    public function __construct()
    {
        add_action('admin_init', [$this, 'init']);
    }

    /**
     * In ye olde days, when this license manager was its own module, it required its own license key to receive updates.
     * 
     * This isn't needed anymore, so let's clean up any entries related to it.
     */
    public function init()
    {
        $valid_licenses = FFWPLM::valid_licenses();

        /**
         * 4163 was the internal product ID of Daan.dev License Manager.
         */
        if (isset($valid_licenses['4163'])) {
            unset($valid_licenses['4163']);
        }

        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_VALID_LICENSES, $valid_licenses);

        /**
         * Update stored version number.
         */
        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_DB_VERSION, $this->version);
    }
}
