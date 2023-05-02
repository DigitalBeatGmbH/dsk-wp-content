<?php

namespace Wcustom\Wdm;

use Wcustom\Wdm\Cron;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.0.1
 * @package    Wcustom
 * @subpackage Wcustom/includes
 * @author     WebZap
 */
class Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function deactivate()
	{
		
		// clean the scheduler/cron
		$plugin_cron = new Cron();
		$plugin_cron->cron_deactivation();
	}

}