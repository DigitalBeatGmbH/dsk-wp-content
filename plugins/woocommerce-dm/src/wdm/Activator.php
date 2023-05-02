<?php

namespace Wcustom\Wdm;

use Wcustom\Wdm\Admin\Admin;

/**
 * Fired after plugin loaded, for inital stuff
 *
 * @since      0.0.1
 * @package    Wcustom
 * @subpackage Wcustom/includes
 * @author     WebZap
 */
class Activator
{
    
    /**
     * The version of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    
    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $version )
    {
        $this->version = $version;
    }

	/**
	 * Install or update our Database scheme
	 *
	 * @since    0.0.1
	 */
    public function update_db_check()
	{
	    global $wpdb;
	    
	    $installed_ver = get_option( "wcustom_db_version" );
	    
	    if ( $installed_ver != $this->version ) {
    	    
    	    update_option( 'wcustom_db_version', $this->version );
    	    
    	    add_action('init', function() {
        	    $plugin_admin = new Admin();
        	    $plugin_admin->import_aux();
    	    }, 50);
	    }
	}

}