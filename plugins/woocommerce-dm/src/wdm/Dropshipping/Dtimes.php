<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;
use Wcustom\Wdm\Dropshipping\TaxBase;

/**
 * The delivery times specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class Dtimes extends TaxBase
{
    protected static $taxonomy = 'dm_dtimes';
    
    /**
     * Start import of units
     *
     * @since    0.0.1
     */
    public function import_dtimes($die = true)
    {
        $return = $this->import('get_handlingtimes');
        
        if($die) {
            wp_send_json($return);
            wp_die();
        }
    }
    
    public static function get_wc_dtime($dm_rate)
    {
        // first find the correct dm tax
        $dm_dtimes = get_terms(array(
            'taxonomy'	 => static::$taxonomy,
            'hide_empty' => false,
            'name' => $dm_rate,
        ));
        
        $dm_rate_id = null;
        if(!empty($dm_dtimes) && !is_wp_error($dm_dtimes)) {
            $dm_rate_id = $dm_dtimes[0]->term_id;
        }
        
        if(empty($dm_rate_id)) {
            return false;
        }
        
        $dtimes = get_field('dm_dtimes', 'acf-options-pdtime');
        if(!empty($dtimes)) {
            foreach($dtimes as $dtime) {
                if(empty($dtime["dm_dtime"])) {
                    return false;
                }
                if($dm_rate_id == $dtime["dm_dtime"]) {
                    return $dtime["wc_dtime"];
                }
            }
        }
        
        return false;
    }
}