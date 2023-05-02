<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 * @copyright © 2022 Daan van den Bergh. All Rights Reserved.
 */
class OmgfPro_DB_Migrate
{
    /** @var string */
    private $current_version = '';

    /**
     * DB Migration constructor.
     */
    public function __construct()
    {
        $this->current_version = get_option(OmgfPro_Admin_Settings::OMGF_PRO_DB_VERSION);

        if ($this->should_run_migration('2.4.0')) {
            new OmgfPro_DB_Migrate_V240();
        }

        if ($this->should_run_migration('3.3.0')) {
            new OmgfPro_DB_Migrate_V330($this->current_version);
        }

        if ($this->should_run_migration('3.6.0')) {
            new OmgfPro_DB_Migrate_V360($this->current_version);
        }
    }

    /**
     * Checks whether migration script has been run.
     * 
     * @param mixed $version 
     * @return bool 
     */
    private function should_run_migration($version)
    {
        return version_compare($this->current_version, $version) < 0;
    }
}
