<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;
use Wcustom\Wdm\Dropshipping\TaxBase;

/**
 * The unit specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class Units extends TaxBase
{
    protected static $taxonomy = 'dm_units';
	
	/**
	 * Start import of units
	 *
	 * @since    0.0.1
	 */
    public function import_units($die = true)
	{
	    $return = $this->import('get_baseprice_unit');
	    
	    if($die) {
	        wp_send_json($return);
	        wp_die();
	    }
	}
	
	/**
	 * 
	 * @param unknown $dm_unit
	 * @return boolean|mixed
	 */
	public static function get_wc_unit($dm_unit)
	{
	    // first find the correct dm tax
	    $dm_units = get_terms(array(
	        'taxonomy'	 => static::$taxonomy,
	        'hide_empty' => false,
	        'name' => $dm_unit,
	    ));

	    $dm_unit_id = null;
	    if(!empty($dm_units) && !is_wp_error($dm_units)) {
	        $dm_unit_id = $dm_units[0]->term_id;
	    }
	    
	    if(empty($dm_unit_id)) {
	        return false;
	    }
	    
	    $dunits = get_field('dm_units', 'acf-options-punit');
	    if(!empty($dunits)) {
    	    foreach($dunits as $unit) {
    	        if(empty($unit["dm_unit"])) {
    	            return false;
    	        }
    	        if($dm_unit_id == $unit["dm_unit"]) {
    	            return $unit["wc_unit"];
    	        }
    	    }
	    }
	    
	    return false;
	}
}