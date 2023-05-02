<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;
use Wcustom\Wdm\Admin\Admin;

/**
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class TaxBase
{
    
    /**
     * The version of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    
    protected static $taxonomy;
    
    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $version = 1 )
    {
        $this->version = $version;
    }
    
    /**
     * Start import of units
     *
     * @since    0.0.1
     */
    public function import($call)
    {
        $curl = new Curl($call);
        
        $return = [];
        if ($curl->error) {
            $return['success'] = false;
            $return['response'] = $curl->errorCode;
            $return['html'] = $curl->errorMessage;
            
            $admin = new Admin();
            $admin->add_flash_notice( $call . ' ' .  __( 'finished with error', 'wcustom' ) . ' ' . $curl->errorCode . ' -- ' . $curl->errorMessage, "error", false );
            add_action( 'admin_notices', [$admin, 'display_flash_notices'], 12 );
        } else {
            $values = $curl->response;
            if(is_array($values)) {
                foreach($values as $id => $text) {
                    $this->add($id, $text);
                }
            } else {
                $return['success'] = false;
                $return['html'] = __('Not recieved anything', 'wcustom');
                
                $admin = new Admin();
                $admin->add_flash_notice( $call . ' ' .  __( 'finished with error', 'wcustom' ) . ' ' . $curl->errorCode . ' -- ' . __('Not recieved anything', 'wcustom'), "error", false );
                add_action( 'admin_notices', [$admin, 'display_flash_notices'], 12 );
            }
            
            $return['success'] = true;
            $return['response'] = print_r($curl->response, true);
            $return['html'] = __('Success', 'wcustom');
        }
        
        return $return;
    }
	
	/**
	 * adding or updating
	 * 
	 * @param int $dm_id
	 * @param varchar $dm_text
	 * @return boolean|unknown
	 */
	public function add($dm_id, $dm_text) 
    {
        
        // check if term with this id exists
        $term = get_term_by('slug', sanitize_title($dm_text), static::$taxonomy);

        if(is_wp_error($term)) {
           error_log($term);
	       return false;
        } elseif(empty($term)) {
	        
	        $term = wp_insert_term(
	            $dm_text, // the term
	            static::$taxonomy, // the taxonomy
    	        array(
    	            'slug' => sanitize_title($dm_text)
    	        )
            );
    	    
    	    // array('term_id'=>12,'term_taxonomy_id'=>34))
	        if(is_array($term)) {
	            $term_id = $term['term_id'];
	            update_field('dm_id', $dm_id, static::$taxonomy . '_' . $term_id);
	            update_field('dm_text', $dm_text, static::$taxonomy . '_' . $term_id);
    	    }
	    } else {
	        update_field('dm_text', $dm_text, static::$taxonomy . '_' . $term->term_id);
	    }
	    
	    return $term;
	}
}