<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;
use Wcustom\Wdm\Dropshipping\TaxBase;

/**
 * The category specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class Categories extends TaxBase
{
    
    protected static $taxonomy = 'product_cat';
    
    public $id_cache = [];
	
	/*
	 * $dm_category is f.e. 'Spielzeuge & Spiele>Spiele'
	 */
    public function get_wc_ids($dm_category, $dm_category_ids)
    {
        
        if(isset($this->id_cache[$dm_category])) {
            return $this->id_cache[$dm_category];
        }
	    
	    $term_paths = explode('|', $dm_category);
        $dm_term_id_paths = explode('|', $dm_category_ids);
	    foreach($term_paths as $key => $term_path) {
	        $dm_term_ids = explode('>', $dm_term_id_paths[$key]);
	        $term_names = explode('>', $term_path);
	        $term_ids = array();
	        for($depth = 0; $depth < count($term_names); $depth++) {
	            $term_parent = ($depth > 0) ? $term_ids[($depth - 1)] : '';
	            
	            $args = array(
	                'hide_empty' => false, // also retrieve terms which are not used yet
	                'meta_query' => array(
	                    array(
	                        'key'       => 'dm_id',
	                        'value'     => $dm_term_ids[$depth],
	                        'compare'   => '='
	                    )
	                ),
	                'taxonomy'  => static::$taxonomy,
	            );
	            
	            $term = false;
	            $terms = get_terms( $args );
	            if(!is_wp_error($terms) && !empty($terms)) {
	                $term = $terms[0];
	            }
	            
	            //if term does not exist, try to insert it.
	            if( $term === false || $term === 0 || $term === null) {
	                $insert_term_args = ($depth > 0) ? array('parent' => $term_ids[($depth - 1)]) : array();
	                $term = wp_insert_term($term_names[$depth], static::$taxonomy, $insert_term_args);
	                if(is_array($term)) {
	                    update_field('dm_id', $dm_term_ids[$depth], 'term_' . $term['term_id']);
	                }
	            }
	            
	            if(is_object($term) && !is_wp_error($term)) {
	                $term_ids[$depth] = intval($term->term_id);
	            } elseif(is_array($term)) {
	                $term_ids[$depth] = intval($term['term_id']);
	            } else {
	                //uh oh.
	                error_log("Couldn't find or create ".static::$taxonomy." with path {$term_path}.\n");
	                break;
	            }
	        }
	        //if we got a term at the end of the path, save the id so we can associate
	        /*if(array_key_exists(count($term_names) - 1, $term_ids)) {
	         $new_post_terms[$tax][] = $term_ids[(count($term_names) - 1)];
	         }*/
	    }
	    
	    $this->id_cache[$dm_category] = $term_ids;
	    return $term_ids;
	}
}