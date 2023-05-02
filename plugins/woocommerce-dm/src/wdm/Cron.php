<?php

namespace Wcustom\Wdm;

use Wcustom\Wdm\Admin\Admin;

/**
 *
 * This class defines all actions for the cron job. 
 *
 * @since      0.0.1
 * @package    Wcustom
 * @subpackage Wcustom/includes
 * @author     WebZap
 */
class Cron
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 */
	public function __construct( $plugin_name )
	{

		$this->plugin_name = $plugin_name;
	}

	/**
	 * Initialize all crons/schedules.
	 * Init for Cron to set function call in Wcustom::define_cron_hooks
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 */
	public function cron_activation()
	{
	    if ( !wp_next_scheduled( 'my_every_min' ) ) {
	        wp_schedule_event( time(), 'every_minute', 'my_every_min' );
	    }
	    
	    if ( !wp_next_scheduled( 'my_daily_event' ) ) {
	        wp_schedule_event( strtotime(date('03:00:00')), 'daily', 'my_daily_event' );
	    }
	    
	    if ( !wp_next_scheduled( 'my_secondhour' ) ) {
	        wp_schedule_event( time(), 'secondhour', 'my_secondhour' );
	    }
	    
	    if ( !wp_next_scheduled( 'my_twelvehour' ) ) {
	        wp_schedule_event( time(), 'twicedaily', 'my_twelvehour' );
	    }
	    
	    add_action( 'my_daily_event',  array($this, 'refresh_db' ));
	    //add_action( 'every_five_min',  array($this, 'every_five_min' ));
	    add_action( 'my_every_min',  array($this, 'my_every_min' ));
	    add_action( 'my_twelvehour',  array($this, 'my_twelvehour' ));
	}
	
	public function my_twelvehour()
	{
	    global $wpdb;
	    @ini_set('max_execution_time', -1);
	    
	    if(get_option( 'woocommerce-dm_get_products' ) == 0) {
	        $admin = new Admin();
	        $admin->import_stock();
	    }
	}
	
	public function my_every_min()
	{
	    global $wpdb;
	    
	    @ini_set('max_execution_time', -1);
	    
	    $is_import_script_running = get_option( 'woocommerce-dm_is_import_script_running' );
	    
	    // if script is running since one hour, it's dead
	    if($is_import_script_running && $is_import_script_running < time() - 3600) {
	        $is_import_script_running = false;
	        delete_option( 'woocommerce-dm_is_import_script_running' );
	    }
	    
	    if(!$is_import_script_running && get_option( 'woocommerce-dm_get_products' ) == 1) {
	        
	        update_option( 'woocommerce-dm_is_import_script_running', time() );
	        $admin = new Admin();
	        $admin->import_all();
	        delete_option( 'woocommerce-dm_is_import_script_running' );
	        
	    }
	}

	public function refresh_db()
	{
		global $wpdb;
		
	}

	public function cron_deactivation()
	{
	    
	}

	/**
	 * Add custom Interval for Cron
	 *
	 */
	public function add_intervals( $schedules )
	{
		$schedules['every_five_min'] = array(
				'interval' => 300,
				'display' => __( 'Once Every 5 Mins' ),
		);
		$schedules['every_fifteen_min'] = array(
		    'interval' => 900,
		    'display' => __( 'Once Every 15 Mins' ),
		);
		$schedules['secondhour'] = array(
		    'interval' => 3600*2,
		    'display' => __( 'Every second hour' ),
		);

		return $schedules;
	}
}
