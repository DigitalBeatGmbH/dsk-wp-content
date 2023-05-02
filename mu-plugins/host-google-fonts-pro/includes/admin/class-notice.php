<?php

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *           
 * @copyright © 2022 Daan van den Bergh. All Rights Reserved.
 */

defined('ABSPATH') || exit;

class OmgfPro_Admin_Notice
{
	const OMGF_ADMIN_NOTICE_TRANSIENT      = 'omgf_admin_notice';
	const OMGF_PRO_ADMIN_NOTICE_TRANSIENT  = 'omgf_pro_admin_notice';
	const OMGF_PRO_ENROLLMENT_TRANSIENT    = 'omgf_pro_enrollment_shown';
	const OMGF_PRO_ADMIN_NOTICE_EXPIRATION = 60;

	/** @var array $notices */
	public static $notices = [];

	/**
	 * @param        $message
	 * @param string $type (info|warning|error|success)
	 * @param string $screen_id
	 * @param string $id
	 */
	public static function set_pro_notice($message, $type = 'success', $id = '', $screen_id = 'all', $expire = self::OMGF_ADMIN_NOTICE_TRANSIENT)
	{
		self::$notices = get_transient(self::OMGF_PRO_ADMIN_NOTICE_TRANSIENT);

		if (!self::$notices) {
			self::$notices = [];
		}

		self::$notices[$screen_id][$type][$id] = $message;

		set_transient(self::OMGF_PRO_ADMIN_NOTICE_TRANSIENT, self::$notices, $expire);
	}

	/**
	 * @param        $message
	 * @param string $type (info|warning|error|success)
	 * @param string $screen_id
	 * @param string $id
	 */
	public static function set_notice($message, $type = 'success', $id = '', $screen_id = 'all')
	{
		self::$notices = get_transient(self::OMGF_ADMIN_NOTICE_TRANSIENT);

		if (!self::$notices) {
			self::$notices = [];
		}

		self::$notices[$screen_id][$type][$id] = $message;

		set_transient(self::OMGF_ADMIN_NOTICE_TRANSIENT, self::$notices, self::OMGF_PRO_ADMIN_NOTICE_EXPIRATION);
	}

	/**
	 * Prints notice (if any)
	 */
	public static function print_notice()
	{
		$admin_notices = get_transient(self::OMGF_PRO_ADMIN_NOTICE_TRANSIENT);

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
							<p><?= $line; ?></p>
						<?php endforeach; ?>
					</div>
<?php
				}
			}
		}

		delete_transient(self::OMGF_PRO_ADMIN_NOTICE_TRANSIENT);
	}
}
