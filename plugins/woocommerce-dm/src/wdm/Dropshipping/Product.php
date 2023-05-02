<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;
use Wcustom\Wdm\Dropshipping\Taxes;
use Wcustom\Wdm\Dropshipping\Categories;
use Wcustom\Wdm\Dropshipping\Media;

/**
 * The product specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class Product
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
	
    
    /**
     * Deactivate all products not in DM list
	 * makes a CURL for get_product_list
     *
     * @since    1.2.1
     */
	public static function deactivate_by_product_list(&$log)
	{
        $curl = new Curl('get_product_list');
        
        if ($curl->error) {
            error_log($curl->errorMessage);
            return false;
        } else {
			$list = $curl->response;
            if(empty($list)) {
                return false;
            }
			return self::deactivate_all($log, $list);
		}
	}
    
    /**
     * Deactivate all products not in list
     *
     * @since    0.0.1
     */
    public static function deactivate_all(&$log, $list)
    {
		// Mail vom 03.04.2020: Wenn der Kunde keine Produkte auf der Aboliste hat oder der DM nicht antwortet. soll bitte keine Deaktivierung mehr erfolgen.
		// Alle Produkte bleiben in diesem Fall unverändert. Der Zeitstempel zur nächsten Aktualisierung wird normal gesetzt.
		if(is_array($list)) {
			$list = array_map('trim', $list);
		} else {
			$list = trim($list);
		}
		if(empty($list)) {
			return false;
		}
		
		$product = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND', // Optional, defaults to "AND"
				array(
					'key' => 'dm_product_id',
					'value' => $list,
					'compare' => 'NOT IN',
				),
				array(
					'key' => 'dm_product_id',
					'compare' => 'EXISTS',
				),
				array(
					'key' => 'dm_product_id',
					'value' => '',
					'compare' => '!=',
				)
			),
			'fields' => 'ids',
		);
		$product_post = get_posts($product);
		
		$products_array = array();
		foreach ($product_post as $v) {
			wp_update_post( array(
				'ID' => $v,
				'post_status' => 'draft',
			));
			$log->deactivate_product($v, null, 'Produkt wurde nicht übertragen');
			
			$objProduct = wc_get_product($v);
			if($objProduct) {
				$objProduct->set_stock_quantity(0);
				$objProduct->set_stock_status('outofstock');
				$objProduct->save();
			}
		}
    }
    
    public function add_base_price($p, $product_id)
    {
        $unit_id = Units::get_wc_unit($p->basepriceunit);
        $unit = get_term($unit_id, 'product_unit');
        if(!empty($unit) && !is_wp_error($unit)) {
            update_post_meta( $product_id, '_unit', $unit->slug );
            
            /**
             *  100g
             *  100ml
             *  100mm
             *  kg
             *  l
             *  m
             *  qm
             */
            switch($p->basepriceunit) {
                case '100ml':
                case '100g':
                case '100mm':
                    update_post_meta( $product_id, '_unit_product', number_format(floatval($p->basepricevalue), 2, ',', '.' ));
                    update_post_meta( $product_id, '_unit_price_regular', round(100 / floatval($p->basepricevalue) * floatval($p->list_price), 2) / 100);
                    update_post_meta( $product_id, '_unit_price_sale', round(100 / floatval($p->basepricevalue) * floatval($p->list_price), 2) / 100);
                    update_post_meta( $product_id, '_unit_base', '100' );
                    break;
                case 'kg':
                case 'l':
                case 'qm':
                    update_post_meta( $product_id, '_unit_product', number_format(floatval($p->basepricevalue), 3, ',', '.' ));
                    update_post_meta( $product_id, '_unit_price_regular', round(1 / floatval($p->basepricevalue) * floatval($p->list_price), 2));
                    update_post_meta( $product_id, '_unit_price_sale', round(1 / floatval($p->basepricevalue) * floatval($p->list_price), 2));
                    update_post_meta( $product_id, '_unit_base', '1' );
                    break;
                case 'm':
                    update_post_meta( $product_id, '_unit_product', number_format(floatval($p->basepricevalue), 3, ',', '.' ));
                    update_post_meta( $product_id, '_unit_price_regular', round(1 / floatval($p->basepricevalue) * floatval($p->list_price), 2));
                    update_post_meta( $product_id, '_unit_price_sale', round(1 / floatval($p->basepricevalue) * floatval($p->list_price), 2));
                    update_post_meta( $product_id, '_unit_base', '1' );
                    break;
            }
        }
    }
    
    /**
     * object(stdClass)#16583 (28) {
     ["product_id"]=>
     int(46322)
     ["product_code"]=>
     string(10) "6762639024"
     ["status"]=>
     string(1) "A"
     ["list_price"]=>
     string(5) "10.97"
     ["amount"]=>
     string(3) "100"
     ["weight"]=>
     string(5) "0.200"
     ["shipping_freight"]=>
     string(4) "0.00"
     ["timestamp"]=>
     string(10) "1427886769"
     ["updated_timestamp"]=>
     string(10) "1567896518"
     ["free_shipping"]=>
     string(1) "N"
     ["tax"]=>
     array(1) {
     [0]=>
     string(18) "MwSt. Standardsatz"
     }
     ["ean"]=>
     string(13) "4007396049107"
     ["handlingtime"]=>
     string(8) "1-2 Tage"
     ["hasbaseprice"]=>
     string(1) "N"
     ["basepriceunit"]=>
     string(0) ""
     ["basepricevalue"]=>
     string(0) ""
     ["lang_code"]=>
     string(2) "de"
     ["product"]=>
     string(29) "6 Nimmt Kartenspiel, 1 Stück"
     ["full_description"]=>
     string(668) "Für 2-10 Spieler. Ab 10 Jahren. Spieldauer: ca. 45 Minuten. bringt viele Minuspunkte. Reihe legt, muss die ersten fünf Karten nehmen. Und das von vier Kartenreihen anlegt. Wer die sechste Karte in eine Jeder Spieler erhält 10 Karten, die er möglichst schlau an eine Ende die wenigsten Hornochsen hat. für jeden Hornochsen einen Minuspunkt ein. Sieger ist, wer am Karten zu kassieren. Jede Karte die man nehmen muss, bringt Kartenspiel, das einen nicht mehr loslässt. Ziel ist es, keine Bei 6 nimmt! ist der Name Programm. Es ist ein raffiniertes den Hornochsen eine zeitgemäße Überarbeitung! Seit über 17 Jahren ein beliebter Klassiker, erhält das Spiel mit"
     ["price"]=>
     float(6.22)
     ["company_id"]=>
     string(2) "33"
     ["company_name"]=>
     string(29) "Power & Handel Vertriebs-GmbH"
     ["category"]=>
     string(26) "Spielzeuge & Spiele>Spiele"
     ["category_ids"]=>
     string(9) "6193>6203"
     ["main_image"]=>
     string(71) "http://www.dropshipping-marktplatz.de/images/detailed/44/6762639024.jpg"
     ["additional_images"]=>
     string(0) ""
     ["product_features"]=>
     string(0) ""
     ["product_options"]=>
     string(0) ""
     }
     *
     * @param unknown $p
     */
    public function import($p, $categories = null)
    {
        $user = get_option('acf-options-custom_user');
        
        if($categories === null) {
            $categories = new Categories();
        }
        
        // check the matching
        if(!empty($p->tax[0])) {
            $wc_tax_rate = Taxes::get_wc_rate($p->tax[0]);
        }
        
        if(!empty($p->handlingtime)) {
            $wc_handling_time = Dtimes::get_wc_dtime($p->handlingtime);
        }
        
        if($p->hasbaseprice == 'Y') {
            $unit_id = Units::get_wc_unit($p->basepriceunit);
            $unit = get_term($unit_id, 'product_unit');
            if(!empty($unit) && !is_wp_error($unit)) {
                $wc_unit = $unit->term_id;
            }
        }
        
        // let's check, if we already know this product
        $objProduct = null;
        $args = array(
            'post_type' => 'product',
            'meta_key' =>  'dm_product_id',
            'meta_value' => $p->product_id,
        );
        $found = wc_get_products($args);
        if(!empty($found)) {
            $objProduct = array_pop($found);
            $found = true;
        }
        if($objProduct) {
            
            // no rate or handling time matched
            if(empty($wc_tax_rate) || empty($wc_handling_time) || (empty($wc_unit) && $p->hasbaseprice == 'Y')) {
                $objProduct->set_status('draft');
                $objProduct->set_stock_quantity(0);
                $objProduct->set_stock_status('outofstock');
                $product_id = $objProduct->save();
                if(empty($wc_tax_rate)) {
                    $message = 'Steuer ' .$p->tax[0]. ' nicht gematcht';
                } elseif(empty($wc_handling_time)) {
                    $message = 'Lieferzeit ' .$p->handlingtime. ' nicht gematcht';
                } else {
                    $message = 'Einheit ' .$p->basepriceunit. ' nicht gematcht';
                }
                return ['type' => 'deactivated', 'id' => $product_id, 'message' => $message];
            }
            
            if(get_option('acf-options-custom_refresh_settings_name')) {
                $objProduct->set_name($p->product);
            }
            if(get_option('acf-options-custom_refresh_settings_description')) {
                $objProduct->set_description($p->full_description);
            }
            if(get_option('acf-options-custom_refresh_settings_prices')) {
                $objProduct->set_price($p->list_price); // set product price
                $objProduct->set_sale_price($p->list_price); // set product price
                $objProduct->set_regular_price($p->list_price); // set product regular price
            }
        }
        
        // if some matching is missing, end here
        if(empty($wc_tax_rate) || empty($wc_handling_time) || (empty($wc_unit) && $p->hasbaseprice == 'Y')) {
            if(empty($wc_tax_rate)) {
                $message = 'Steuer ' .$p->tax[0]. ' nicht gematcht';
            } elseif(empty($wc_handling_time)) {
                $message = 'Lieferzeit ' .$p->handlingtime. ' nicht gematcht';
            } else {
                $message = 'Einheit ' .$p->basepriceunit. ' nicht gematcht';
            }
            return ['type' => 'deactivated', 'id' => 0, 'message' => $message];
        }
        
        // it's a new product
        if(empty($objProduct)) {
            $objProduct = new \WC_Product();
            $objProduct->set_name($p->product);
            $objProduct->set_description($p->full_description);
            $objProduct->set_price($p->list_price); // set product price
            $objProduct->set_sale_price($p->list_price); // set product price
            $objProduct->set_regular_price($p->list_price); // set product regular price
            
            $objProduct->set_category_ids($categories->get_wc_ids($p->category, $p->category_ids)); // array of category ids, You can get category id from WooCommerce Product Category Section of Wordpress Admin
        }
        
        $objProduct->set_status($p->status == 'A' ? "publish" : 'draft');  // can be publish,draft or any wordpress post status
        $objProduct->set_catalog_visibility('visible'); // add the product visibility status
        $objProduct->set_sku($p->product_code); //can be blank in case you don't have sku, but You can't add duplicate sku's
        $objProduct->set_manage_stock(true); // true or false
        $objProduct->set_stock_quantity($p->status == 'A' ? $p->amount : 0);
        $objProduct->set_stock_status($p->amount > 0 && $p->status == 'A' ? 'instock' : 'outofstock'); // in stock or out of stock value
        $objProduct->set_backorders('no');
        $objProduct->set_weight($p->weight);
        
        if(!empty($p->tax[0])) {
            $objProduct->set_tax_class($wc_tax_rate);
        }
        
        if(!empty($p->handlingtime)) {
            $delivery_time_id = $wc_handling_time;
        }
        
        $product_id = $objProduct->save(); // it will save the product and return the generated product id
        
        // if WooCommerce Germanized is installed....
        if(function_exists('wc_ts_set_crud_data')) {
            
            if(get_option('acf-options-custom_refresh_settings_ean') || !$found) {
                wc_ts_set_crud_data( $objProduct, '_ts_gtin', wc_clean( $p->ean ) );
            }
            wc_ts_set_crud_data( $objProduct, '_ts_mpn', wc_clean( $p->product_code ) );
            
            if(get_option('acf-options-custom_refresh_settings_prices') || !$found) {
                if($p->hasbaseprice == 'Y') {
                    $this->add_base_price($p, $product_id);
                }
            }
            
            if($p->free_shipping == 'Y') {
                wc_ts_set_crud_data( $objProduct, '_free_shipping', 'yes' );
            } else {
                wc_ts_set_crud_data( $objProduct, '_free_shipping', 'no' );
            }
            
            $objProduct->save(); // it will save the product and return the generated product id
        }
        
        Media::set_product_media($p, $objProduct, $product_id);
        
        update_field( 'dm_ek', wc_clean( $p->price ), $product_id );
        update_field( 'dm_ean', wc_clean( $p->ean ), $product_id );
        update_field( 'dm_sku', wc_clean( $p->product_code ), $product_id );
        update_field( 'dm_product_id', wc_clean( $p->product_id ), $product_id );
        update_field( 'dm_name', wc_clean( $p->product ), $product_id );
        update_field( 'dm_manufacturer', wc_clean( $p->company_name ), $product_id );
        update_field( 'dm_description', wc_clean( $p->full_description ), $product_id );
        
        // Replace the new product_delivery_time in the product
        if( $delivery_time_id/* && !has_term( $delivery_time_id, 'product_delivery_time', $product_id )*/) {
            wp_set_object_terms($product_id, $delivery_time_id, 'product_delivery_time' );
        }
        
        // add product to a user
        if(!empty($user)) {
            $arg = array(
                'ID' => $product_id,
                'post_author' => $user,
            );
            wp_update_post( $arg );
            
            if(class_exists('WCFMmp_Admin')) {
                $_POST['wcfmmp_store'] = $user;
                \WCFMmp_Admin::wcfmmp_store_product_data_save($product_id);
            }
        }
        
        if($found) {
            return ['type' => 'updated', 'id' => $product_id];
        } else {
            return ['type' => 'added', 'id' => $product_id];
        }
    }
    
    /**
     * object(stdClass)#16994 (2) {
     ["productid"]=>
     string(5) "39394"
     ["amount"]=>
     string(3) "136"
     }
     */
    public function update_stock($p)
    {
        
        $objProduct = null;
        $args = array(
            'post_type' => 'product',
            'meta_key' =>  'dm_product_id',
            'meta_value' => $p->productid,
        );
        $found = wc_get_products($args);
        if(!empty($found)) {
            $objProduct = array_pop($found);
        }
        /*
         * this section is for "do not publich products automatically
         * if($objProduct) {
            $objProduct->set_stock_quantity($objProduct->get_status() == 'publish' ? $p->amount : 0);
            $objProduct->set_stock_status($p->amount > 0 && $objProduct->get_status() == 'publish' ? 'instock' : 'outofstock'); // in stock or out of stock value
            
            if($p->amount > 0 && $objProduct->get_status() == 'publish') {
                $objProduct->set_status("publish");  // can be publish,draft or any wordpress post status
                
                $objProduct->save();
                
                // And finally (optionally if needed)
                wc_delete_product_transients( $objProduct->get_id() ); // Clear/refresh the variation cache
                
                return ['type' => 'updated', 'id' => $objProduct->get_id()];
            } else {
                $objProduct->set_status('draft');  // can be publish,draft or any wordpress post status
                
                $objProduct->save();
                
                // And finally (optionally if needed)
                wc_delete_product_transients( $objProduct->get_id() ); // Clear/refresh the variation cache
                
                return [
                    'type' => 'deactivated', 
                    'id' => $objProduct->get_id(), 
                    'message' => ($p->amount <= 0 ? 'Lagerbestand ist 0' : 'Produkt ist nicht veröffentlicht'),
                ];
            }
        }
        */
        if($objProduct) {
            
            if($p->amount > 0) {
                $objProduct->set_stock_quantity($p->amount);
                $objProduct->set_stock_status('instock'); // in stock or out of stock value
                $objProduct->set_status("publish");  // can be publish,draft or any wordpress post status
                
                $objProduct->save();
                
                // And finally (optionally if needed)
                wc_delete_product_transients( $objProduct->get_id() ); // Clear/refresh the variation cache
                
                return ['type' => 'updated', 'id' => $objProduct->get_id()];
            } else {
                $objProduct->set_stock_quantity(0);
                $objProduct->set_stock_status('outofstock'); // in stock or out of stock value
                $objProduct->set_status('draft');  // can be publish,draft or any wordpress post status
                
                $objProduct->save();
                
                // And finally (optionally if needed)
                wc_delete_product_transients( $objProduct->get_id() ); // Clear/refresh the variation cache
                
                return [
                    'type' => 'deactivated',
                    'id' => $objProduct->get_id(),
                    'message' => 'Lagerbestand ist 0',
                ];
            }
        }
        
        return false;
    }
}