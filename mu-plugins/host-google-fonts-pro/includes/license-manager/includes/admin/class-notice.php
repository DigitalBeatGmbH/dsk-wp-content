<?php

/**
 * @package   Daan.dev License Manager
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2020 - 2022 Daan van den Bergh. All Rights Reserved.
 */

defined('ABSPATH') || exit;

class FFWPLM_Admin_Notice
{
    const FFWP_LICENSE_MANAGER_ADMIN_NOTICE_TRANSIENT = 'ffwp_license_manager_admin_notice';

    /** @var array $notices */
    public static $notices = [];

    /**
     * @param        $message
     * @param string $type (info|warning|error|success)
     * @param string $message_id
     * @param string $screen_id
     */
    public static function set_notice($message, $type = 'success', $message_id = '', $screen_id = 'all', $expiration = 30)
    {
        self::$notices                                 = get_transient(self::FFWP_LICENSE_MANAGER_ADMIN_NOTICE_TRANSIENT);
        self::$notices[$screen_id][$type][$message_id] = $message;

        set_transient(self::FFWP_LICENSE_MANAGER_ADMIN_NOTICE_TRANSIENT, self::$notices, $expiration);
    }

    /**
     * Prints notice (if any)
     */
    public static function print_notice()
    {
        $admin_notices = get_transient(self::FFWP_LICENSE_MANAGER_ADMIN_NOTICE_TRANSIENT);

        if (is_array($admin_notices)) {
            $current_screen = get_current_screen();

            foreach ($admin_notices as $screen => $notice) {
                if ($current_screen->id != $screen && $screen != 'all') {
                    continue;
                }

                foreach ($notice as $type => $message) {
?>
                    <div id="message" class="notice notice-<?php echo $type; ?> is-dismissible">
                        <?php foreach ($message as $line) : ?>
                            <p><strong><?= $line; ?></strong></p>
                        <?php endforeach; ?>
                    </div>
<?php
                }
            }
        }

        delete_transient(self::FFWP_LICENSE_MANAGER_ADMIN_NOTICE_TRANSIENT);
    }
}
