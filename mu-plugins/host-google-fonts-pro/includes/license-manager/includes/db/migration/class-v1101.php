<?php
defined('ABSPATH') || exit;

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */
class FFWPLM_DB_Migration_V1101
{
    private $version = '1.10.1';

    public function __construct()
    {
        add_action('admin_init', [$this, 'init']);
    }

    /**
     * Not actually a DB upgrade, but it's a nice workaround to make sure the encryption key is installed upon update.
     */
    public function init()
    {
        FFWPLM::install_encryption_key();

        /**
         * Update stored version number.
         */
        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_DB_VERSION, $this->version);
    }
}
