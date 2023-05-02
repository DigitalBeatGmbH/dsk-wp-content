<?php

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */

defined('ABSPATH') || exit;

class FFWPLM_DB_Migration_V140
{
    private $version = '1.4.0';

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        /**
         * Encrypt stored licensed products.
         */
        $licensed_products = get_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY);

        foreach ($licensed_products as &$product) {
            $product['key'] = FFWPLM::encrypt($product['key']);
        }

        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_SETTING_LICENSE_KEY, $licensed_products);

        /**
         * Encrypt stored valid licenses.
         */
        $valid_licenses = FFWPLM::valid_licenses();

        foreach ($valid_licenses as &$license) {
            $license['license'] = FFWPLM::encrypt($license['license']);
        }

        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_VALID_LICENSES, $valid_licenses);

        /**
         * Update stored version number.
         */
        update_option(FFWPLM_Admin::FFWP_LICENSE_MANAGER_OPTION_DB_VERSION, $this->version);
    }
}
