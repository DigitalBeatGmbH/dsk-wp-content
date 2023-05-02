<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;

/**
 * The media specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class Media
{
    
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
	
    public static function set_product_media($p, &$objProduct, $product_id)
	{
	    // above function uploadMedia, I have written which takes an image url as an argument and upload image to wordpress and returns the media id
	    // later we will use this id to assign the image to product.
	    $productImagesIDs = array(); // define an array to store the media ids.
	    if(!empty($p->additional_images)) {
	        $images = array_merge([$p->main_image],$p->additional_images); // images url array of product
	    } else {
	        $images = [$p->main_image];
	    }
	    
	    $images = array_splice($images, 0, get_option('acf-options-custom_max_images', 15) - 1); // remove everything more than 10 images
	    foreach($images as $image){
	        $mediaID = self::uploadMedia($image, $product_id); // calling the uploadMedia function and passing image url to get the uploaded media id
	        
	        if($mediaID) {
    	        update_field('dm_url', $image, $mediaID); // add the dropshipping url
    	        $productImagesIDs[] = $mediaID; // storing media ids in a array.
	        }
	    }
	    if($productImagesIDs){
	        $objProduct->set_image_id($productImagesIDs[0]); // set the first image as primary image of the product
	        
	        //in case we have more than 1 image, then add them to product gallery.
	        if(count($productImagesIDs) > 1){
	            $objProduct->set_gallery_image_ids($productImagesIDs);
	        }
	        $objProduct->save(); // it will save the product and return the generated product id
	    }
	}
	
	public static function uploadMedia($image_url, $product_id)
	{
	    // check if image exists
	    $attachments = get_posts(array(
	        'post_type' => 'attachment',
	        'meta_key' => 'dm_url',
	        'post_parent' => $product_id,
	        'meta_value' => $image_url,
	    ));
	    if(!empty($attachments[0])) {
	        return $attachments[0]->ID;
	    }
	    
	    if(!function_exists('media_sideload_image')) {
    	    require_once(ABSPATH . 'wp-admin/includes/media.php');
    	    require_once(ABSPATH . 'wp-admin/includes/file.php');
    	    require_once(ABSPATH . 'wp-admin/includes/image.php');
	    }
	    
	    // no, we have to upload it
	    $media = media_sideload_image($image_url, $product_id);
	    $attachments = get_posts(array(
	        'post_type' => 'attachment',
	        'post_status' => null,
	        'post_parent' => $product_id,
	        'orderby' => 'post_date',
	        'order' => 'DESC'
	    ));
	    if(!empty($attachments[0])) {
	        return $attachments[0]->ID;
	    } else {
	        return false;
	    }
	}
}