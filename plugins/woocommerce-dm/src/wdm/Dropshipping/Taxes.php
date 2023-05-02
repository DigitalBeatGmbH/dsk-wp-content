<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;
use Wcustom\Wdm\Dropshipping\TaxBase;

/**
 * The tax specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class Taxes extends TaxBase
{
    protected static $taxonomy = 'dm_taxes';
    
	/**
	 * Start import of units
	 *
	 * @since    0.0.1
	 */
    public function import_taxes($die = true)
	{
	    $return = $this->import('get_tax');
	    
	    if($die) {
	        wp_send_json($return);
	        wp_die();
	    }
	}
	
	public static function get_wc_rates()
	{
	    $sections = array(
	        'standard' => __( 'Standard rates', 'woocommerce' ),
	    );
	    
	    // Get tax classes and display as links.
	    $tax_classes = \WC_Tax::get_tax_classes();
	    foreach ( $tax_classes as $class ) {
	        $sections[ sanitize_title( $class ) ] = sprintf( __( '%s rates', 'woocommerce' ), $class );
	    }
	    
	    return $sections;
	}
	
	/*
	 * dm_rate is f.e. 'MwSt. Standard'
	 */
	public static function get_wc_rate($dm_rate)
	{
	    $sections = Taxes::get_wc_rates();
	    
	    // first find the correct dm tax
	    $dm_taxes = get_terms(array(
	        'taxonomy'	 => static::$taxonomy,
	        'hide_empty' => false,
	        'name' => $dm_rate,
	    ));

	    if(!empty($dm_taxes) && !is_wp_error($dm_taxes)) {
	        // now find the option for this id
	        return get_option( 'woocommerce-dm_tax_' . $dm_taxes[0]->term_id);
	    }
	    
	    return false;
	}
	
	public function taxes_form_response()
	{
	    if( isset( $_POST['taxes_form_response_nonce'] ) && wp_verify_nonce( $_POST['taxes_form_response_nonce'], 'taxes_form_response_nonce') ) {
	        
	        $dm_taxes = get_terms(array(
	            'taxonomy'	 => 'dm_taxes',
	            'hide_empty' => false,
	        ));
	        foreach($dm_taxes as $dm_tax) {
	            if(isset($_POST["wcdm_tax_".$dm_tax->term_id])) {
	                update_option( 'woocommerce-dm_tax_' . $dm_tax->term_id, $_POST["wcdm_tax_".$dm_tax->term_id]);
	            }
	        }
	        
	        // redirect the user to the appropriate page
	        wp_redirect( esc_url_raw( add_query_arg( array(
	               'admin_add_notice' => "success",
	               'response' => $_POST,
    	        ),
	            admin_url('admin.php?page=wcustom-ptax' )
            ) ) );
	        exit;
	    }
	    else {
	        wp_die( __( 'Invalid nonce specified', 'wcustom-ptax' ), __( 'Error', 'wcustom-ptax' ), array(
	            'response' 	=> 403,
	            'back_link' => 'admin.php?page=wcustom-ptax',
	        ) );
	    }
	}
}