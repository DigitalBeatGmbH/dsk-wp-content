<?php

namespace Wcustom\Wdm\Admin;

use Wcustom\Wdm\Helper;
use Wcustom\Wdm\Dropshipping\Curl;
use Wcustom\Wdm\Dropshipping\Product;
use Wcustom\Wdm\Dropshipping\Log;
use Wcustom\Wdm\Dropshipping\Dtimes;
use Wcustom\Wdm\Dropshipping\Taxes;
use Wcustom\Wdm\Dropshipping\Units;
use Wcustom\Wdm\Dropshipping\Categories;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/admin
 * @author     WebZap
 */
class Admin
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
    public function __construct( $version = 1 )
    {
        $this->version = $version;
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles()
	{
	    wp_enqueue_style( 'wp-jquery-ui-dialog' );
	    wp_enqueue_style( 'wcustom-admin', WP_PLUGIN_URL . '/woocommerce-dm/assets/admin/css/wcustom-admin.css', array(), $this->version, 'all' );
	}
	
	public function my_admin_dequeue()
	{
	    
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts()
	{
        global $post;
        
	    wp_enqueue_script( 'jquery-ui-dialog', '', array( 'jquery', 'jquery-ui' ));
	    wp_enqueue_script( 'wcustom-admin', WP_PLUGIN_URL . '/woocommerce-dm/assets/admin/js/wcustom-admin.js', array( 'jquery' ), $this->version, false );
        
        $adminVars = [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ];
        
        wp_localize_script( 'wcustom-admin', 'adminVars', $adminVars );
        
        $screen = get_current_screen();
        if(is_object($screen) && 
            (
                $screen->id == 'dropshipping-market_page_wcustom-pdtime' || 
                $screen->id == 'dropshipping-market_page_wcustom-punit' ||
                $screen->id == 'dropshipping-market_page_wcustom-ptax' ||
                $screen->id == 'dropshipping-marktplatz_page_wcustom-pdtime' ||
                $screen->id == 'dropshipping-marktplatz_page_wcustom-punit' ||
                $screen->id == 'dropshipping-marktplatz_page_wcustom-ptax'
            )
           ) {
            //wcustom-pdtime
            wp_enqueue_script( 'wcustom-acf-extension', WP_PLUGIN_URL . '/woocommerce-dm/assets/admin/js/dynamic-select-on-select.js', ['acf-input']);
        }
	}
	
	/**
	 * Register a Optionspage in the admin area.
	 *
	 * @since    0.0.1
	 */
	public function add_plugin_options_admin_page()
	{
	    if (function_exists('acf_add_options_page') && current_user_can( 'activate_plugins' )) {
	        acf_add_options_page(array(
	            'menu_title' => __('Dropshipping Market', 'wcustom'),
	            'page_title' 	=> __('Dropshipping Market', 'wcustom'),
	            'menu_slug' => 'wcustom-options',
	            'capability' => 'edit_posts',
	            'redirect' => false,
	            'post_id' => 'acf-options-custom',
	        ));
	        acf_add_options_page(array(
	            'menu_title' => __('Product Delivery Time', 'wcustom'),
	            'page_title' 	=> __('Product Delivery Time', 'wcustom'),
	            'menu_slug' => 'wcustom-pdtime',
	            'capability' => 'edit_posts',
	            'redirect' => false,
	            'post_id' => 'acf-options-pdtime',
	            'parent_slug' => 'wcustom-options',
	        ));
	        acf_add_options_page(array(
	            'menu_title' => __('Product Unit', 'wcustom'),
	            'page_title' 	=> __('Product Unit', 'wcustom'),
	            'menu_slug' => 'wcustom-punit',
	            'capability' => 'edit_posts',
	            'redirect' => false,
	            'post_id' => 'acf-options-punit',
	            'parent_slug' => 'wcustom-options',
	        ));
	    }
	}
	
	/**
	 * Register a Optionspage in the admin area.
	 *
	 * @since    0.0.1
	 */
	public function add_plugin_actions_admin_page()
	{
	    if (current_user_can( 'activate_plugins' )) {
			add_submenu_page(
			    'wcustom-options',
			    __('Product Tax', 'wcustom'),
			    __('Product Tax', 'wcustom'),
			    'edit_posts',
			    'wcustom-ptax',
			    array( $this, 'display_taxes_admin_page' )
		    );
			/*add_submenu_page(
				'wcustom-options',
				__('Actions', 'wcustom'),
				__('Actions', 'wcustom'),
				'edit_posts',
				'wcustom-actions',
				array( $this, 'display_actions_admin_page' )
		    );*/
		}
	}
	
	/**
	 * Renders the Taxes Page
	 *
	 * @since    0.0.1
	 */
	public function display_taxes_admin_page()
	{
	    $helper = new Helper();
	    
	    if (current_user_can( 'activate_plugins' )) {
	        $dm_taxes = get_terms(array(
	            'taxonomy'	 => 'dm_taxes',
	            'hide_empty' => false,
	        ));
	        include( $helper->template_selector( 'taxes-page', true ) );
	    }
	    
	}
	
	/**
	 * Renders the Actions Page
	 *
	 * @since    0.0.1
	 */
	public function display_actions_admin_page()
	{
	    $helper = new Helper();
	    
	    if (current_user_can( 'activate_plugins' )) {
	        include( $helper->template_selector( 'action-page', true ) );
		}
	    
	}
	
	/**
	 * Just return code 200
	 *
	 * @since    0.0.1
	 */
	public function check()
	{
	    wp_die();
	}
	
	/**
	 * Set the flag to get all products from API
	 *
	 * @since    0.0.1
	 */
	public function set_flag()
	{
	    $helper = new Helper();
	    
	    //if ($_SERVER['REMOTE_ADDR'] == '148.251.192.171' || $_SERVER['REMOTE_ADDR'] == '2a01:4f8:211:e23::2') {
	        update_option( 'woocommerce-dm_get_products', 1 );
	    //}
	    wp_die();
	}
	
	public function import_aux()
	{
	    $plugin_dtimes = new Dtimes($this->version);
	    $plugin_dtimes->import_dtimes(false);
	    
	    $plugin_units = new Units($this->version);
	    $plugin_units->import_units(false);
	    
	    $plugin_tax = new Taxes($this->version);
	    $plugin_tax->import_taxes(false);
	}
	
	/**
	 * Start import of all products
	 *
	 * @since    0.0.1
	 */
	public function import_all()
	{
	    $limit = get_option('acf-options-custom_max_products', 250);
	    
	    if(get_option('woocommerce-dm_get_products') != '1') {
	        return false;
	    }
	    
	    // check running import flag
	    $flag = get_option( 'woocommerce-dm_import_flag');
	    
	    if(empty($flag)) {
	        $flag = time();
	        update_option( 'woocommerce-dm_import_flag', $flag );
	    
    	    $this->import_aux();
    	    
    	    $curl = new Curl('get_product_list_count');
    	    
    	    $return = [];
    	    if ($curl->error) {
    	        $return['success'] = false;
    	        $return['response'] = $curl->errorCode;
    	        $return['html'] = $curl->errorMessage;
    	        error_log(print_r($return, true));
    	        return false;
    	    } else {
    	        $count = $curl->response;
    	        update_option( 'woocommerce-dm_import_flag_count_' . $flag, $count );
    	    }
    	    
    	    // create new log
	        $log = new Log('complete');
	        $log->setGot_nr($count);
	        
	        $start_count = 0;
	        
	        // Get the timestamp for the next event.
	        $timestamp = wp_next_scheduled( 'my_secondhour' );
	        
	        // Remove next event
	        wp_unschedule_event( $timestamp, 'my_secondhour' );
	        
	        // Add first event in 2 hours
	        wp_schedule_event( time() + 7200, 'secondhour', 'my_secondhour' );
	        
	        // now get all product IDs available
	        Product::deactivate_by_product_list($log);
	        
	        $log_id = $log->save();
	        update_option( 'woocommerce-dm_import_flag_log_' . $flag, $log_id );
	    } else {
	        
	        // import already running, catch the old data like count and Log
	        $start_count = get_option( 'woocommerce-dm_import_flag_start_count_' . $flag, 0 );
	        $count = get_option( 'woocommerce-dm_import_flag_count_' . $flag );
	        $log_id = get_option( 'woocommerce-dm_import_flag_log_' . $flag );
	        $log = new Log('complete');
	        $log->load($log_id);
	    }

	    // already finished? close it.
        if($start_count >= $count) {
            delete_option( 'woocommerce-dm_import_flag_start_count_' . $flag );
            delete_option( 'woocommerce-dm_import_flag_count_' . $flag );
            delete_option( 'woocommerce-dm_import_flag_log_' . $flag );
            delete_option( 'woocommerce-dm_import_flag' );
            update_option( 'woocommerce-dm_get_products', 0 );
            
            $log->save(true);
            return;
        }
	    
        $until_count = $start_count+$limit;
        if($until_count > $count) {
            $until_count = $count;
        }
        $log->setStart($start_count);
        $log->setUntil($until_count);
        update_option( 'woocommerce-dm_import_flag_start_count_' . $flag, $until_count+1 );
        
        // import next batch
        $ids_curl = new Curl('get_product_list?begin='.$start_count.'&end='.$until_count);
        if ($ids_curl->error) {
            $return['success'] = false;
            $return['response'] = $ids_curl->errorCode;
            $return['html'] = $ids_curl->errorMessage;
            error_log($ids_curl->errorMessage);
        } else {
            $products_curl = new Curl("get_products/".implode(',', $ids_curl->response));
            if ($products_curl->error) {
                $return['success'] = false;
                $return['response'] = $products_curl->errorCode;
                $return['html'] = $products_curl->errorMessage;
                error_log($products_curl->errorMessage);
            } else {
				
				$products = $products_curl->response->products ?? $products_curl->response;
                if(!empty($products)) {
                    $categories = new Categories();
	                foreach($products as $p) {
	                    @set_time_limit( 0 );
    	                if(!empty($p)) {
    	                    $start = time();
    	                    $product = new Product();
    	                    $pimport = $product->import($p, $categories);
    	                    if($pimport['type'] == 'added') {
    	                        $log->add_product($pimport['id'], $p);
    	                    } elseif($pimport['type'] == 'deactivated') {
    	                        $log->deactivate_product($pimport['id'], $p, $pimport['message']);
    	                    } else{
    	                        $log->update_product($pimport['id'], $p);
    	                    }
    	                    $end = time() - $start;
    	                    if($end > 10) {
                                error_log('produkt '.$p->product_code.' LONG: '.$end);
    	                    } else {
    	                        error_log('produkt '.$p->product_code.' SHORT: '.$end);
    	                    }
    	                }
        	        }
                }
            }
        }
        
        $log->save();
        
        $return['success'] = true;
        $return['html'] = __('Success');
        
        //error_log(print_r($return, true));
	    //wp_send_json($return);
	    //wp_die();
	}
	
	/**
	 * object(stdClass)#16994 (2) {
          ["productid"]=>
          string(5) "39394"
          ["amount"]=>
          string(3) "136"
        }
	 * 
	 * Start import of stock data
	 *
	 * @since    0.0.1
	 */
	public function import_stock()
	{
	    $products_curl = new Curl('get_product_stock_list');
	    
	    $return = [];
	    if ($products_curl->error) {
	        $return['success'] = false;
	        $return['response'] = $products_curl->errorCode;
	        $return['html'] = $products_curl->errorMessage;
	        error_log($products_curl->errorMessage);
	    } else {
	        $log = new Log('stock');
			
			$list = [];
            foreach($products_curl->response as $p) {
                if(is_object($p) && !empty($p->productid)) {
					$list[] = $p->productid;
				}
			}
	        Product::deactivate_all($log, $list);

	        foreach($products_curl->response as $p) {
	            @set_time_limit( 0 );
                if(is_object($p) && !empty($p->productid)) {
                    $product = new Product();
                    $pimport = $product->update_stock($p);

                    if($pimport['type'] == 'updated') {
                        $log->update_product($pimport['id'], $p);
                    } else {
                        $log->deactivate_product($pimport['id'], $p, $pimport['message']);
                    }
                }
            }
	        
	        $log->save();
	        
	        $return['success'] = true;
	        $return['response'] = print_r($products_curl->response, true);
	        $return['html'] = __('Success');
	    }
	    
	    wp_send_json($return);
	    wp_die();
	}
	
	public function add_single_order_action_button($actions)
	{
	    $actions["woocommerce-dm-senddm"] = __( 'Send to Dropshipping Marketplace', 'wcustom' );
	    return $actions;
	}
	
	// Add your custom order status action button (for orders with "processing" status)
	public function add_list_order_action_button( $actions, $order )
	{
	    // Display the button for all orders that have a 'processing' status
	    //if ( $order->has_status( array( 'processing' ) ) ) {
	        
	        // The key slug defined for your action button
	        $action_slug = 'senddm';
	        
	        // Set the action button
	        $actions[$action_slug] = array(
	            'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce-dm-senddm&order_id=' . $order->get_id() ), 'woocommerce-dm-senddm' ),
	            'name'      => __( 'Send to Dropshipping Marketplace', 'wcustom' ),
	            'action'    => $action_slug,
	        );
	    //}
	    return $actions;
	}
	
	// Set Here the WooCommerce icon for your action button
	public function add_custom_order_status_actions_button_css()
	{
	    $action_slug = "senddm"; // The key slug defined for your action button
	    
	    echo '<style>.wc-action-button-'.$action_slug.'::after { font-family: woocommerce !important; content: "\e029" !important; }</style>';
	}
	
	public function woocommerce_dm_senddm($order)
	{
	    global $wp_filesystem;
	    
	    if(!is_object($order) && !empty($_GET['order_id'])) {
	        $order = wc_get_order($_GET['order_id']);
	    }
	    
	    $success == false;
	    if(is_object($order)) {
	        $op = $order->get_items();
	        
	        if(!empty($op)) {
	            $file = '"url";"token";"product_id";"options";"shipping_information";"lang_code";"amount"';
	            $file .= "\n";
	            
	            $url = get_option('acf-options-custom_yoururl');
	            $token = get_option('acf-options-custom_token');
	            
	            if($order->has_shipping_address()) {
	                $firstname = $order->get_shipping_first_name();
	                $lastname = $order->get_shipping_last_name();
    	            $company = $order->get_shipping_company();
    	            
    	            $address1 = $order->get_shipping_address_1();
    	            $pattern = '/(?=\d)/';
    	            $add1parts = preg_split($pattern, $address1, 2);
    	            $address1_street = trim($add1parts[0] ?? '');
    	            $address1_nr = $add1parts[1] ?? '';
    	            
    	            $address2 = $order->get_shipping_address_2();
    	            $city = $order->get_shipping_city();
    	            $postcode = $order->get_shipping_postcode();
    	            $country = $order->get_shipping_country();
	            } else {
	                $firstname = $order->get_billing_first_name();
	                $lastname = $order->get_billing_last_name();
	                $company = $order->get_billing_company();
	                
	                $address1 = $order->get_billing_address_1();
	                $pattern = '/(?=\d)/';
	                $add1parts = preg_split($pattern, $address1, 2);
	                $address1_street = trim($add1parts[0] ?? '');
	                $address1_nr = $add1parts[1] ?? '';
	                
	                $address2 = $order->get_billing_address_2();
	                $city = $order->get_billing_city();
	                $postcode = $order->get_billing_postcode();
	                $country = $order->get_billing_country();
	            }
	            
	            $shipping_information = "$company|$firstname|$lastname|$address2|$address1_street|$address1_nr|$postcode|$city|$country";
	            $lang_code = "de";
	            
	            $dm_products = false;
	            foreach($op as $vop) {
	                $product = $vop->get_product();
	                
	                if(get_field('dm_product_id', $vop->get_product_id())) {
    	                $dm_products = true;
    	                $file .= "\"".addslashes($url)."\";\"".addslashes($token)."\";\"".intval(get_field('dm_product_id', $vop->get_product_id()))."\";;\"".addslashes($shipping_information)."\";\"".addslashes($lang_code)."\";\"".intval($vop->get_quantity())."\"";
    	                $file .= "\n";
	                }
	            }
	            
	            if(empty($dm_products)) {
                    $this->add_flash_notice( __( 'Order has NOT been send to Dropshipping Marketplace', 'wcustom' ), "error", false );
	                wp_redirect(admin_url('/edit.php?post_type=shop_order'), 301);
	                exit;
	            }
	            
	            if($this->connect_fs()) {
    	            
    	            $wp_filesystem->put_contents( trailingslashit( $wp_filesystem->wp_content_dir() ) . 'uploads/file.csv', $file, FS_CHMOD_FILE );
    	            
    	            // wp_mail( string|array $to, string $subject, string $message, string|array $headers = '', string|array $attachments = array() )
    	            if(wp_mail('order@dropshipping-marktplatz.de', 'Neue Bestellung', 'Neue Bestellung', ['Cc: ' . get_option('admin_email')], [trailingslashit( WP_CONTENT_DIR ) . 'uploads/file.csv'])) {
    	                $this->add_flash_notice( __( 'Order has been send to Dropshipping Marketplace', 'wcustom' ), "info", false );
    	                $success = true;
    	            }
    	            
    	            @unlink(WP_CONTENT_DIR . '/uploads/file.csv');
	            }
	        }
	    }
	    
	    if(!$success) {
	        $this->add_flash_notice( __( 'Order has NOT been send to Dropshipping Marketplace', 'wcustom' ), "error", false );
	    }
	    
	    wp_redirect(admin_url('/edit.php?post_type=shop_order'), 301);
	    exit;
	}
	
	/**
	 * Add a flash notice to {prefix}options table until a full page refresh is done
	 *
	 * @param string $notice our notice message
	 * @param string $type This can be "info", "warning", "error" or "success", "warning" as default
	 * @param boolean $dismissible set this to TRUE to add is-dismissible functionality to your notice
	 * @return void
	 */
	public function add_flash_notice( $notice = "", $type = "warning", $dismissible = true )
	{
	    // Here we return the notices saved on our option, if there are not notices, then an empty array is returned
	    $notices = get_option( "my_flash_notices", array() );
	    
	    $dismissible_text = ( $dismissible ) ? "is-dismissible" : "";
	    
	    // We add our new notice.
	    $notices[md5($notice)] = array(
	        "notice" => $notice,
	        "type" => $type,
	        "dismissible" => $dismissible_text
	    );
	    
	    // Then we update the option with our notices array
	    update_option("my_flash_notices", $notices );
	}
	
	/**
	 * Function executed when the 'admin_notices' action is called, here we check if there are notices on
	 * our database and display them, after that, we remove the option to prevent notices being displayed forever.
	 * @return void
	 */
	public function display_flash_notices()
	{
	    $notices = get_option( "my_flash_notices", array() );
	    
	    // Iterate through our notices to be displayed and print them.
	    foreach ( $notices as $notice ) {
	        printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
	            $notice['type'],
	            $notice['dismissible'],
	            $notice['notice']
	            );
	    }
	    
	    // Now we reset our options to prevent notices being displayed forever.
	    if( ! empty( $notices ) ) {
	        delete_option( "my_flash_notices", array() );
	    }
	}
	
	/**
	 * after saving the token, start a settings import
	 */
	public function import_settings()
	{
	    $screen = get_current_screen();
	    
	    if (strpos($screen->id, "wcustom-options") !== false) {
	        $this->import_aux();
	    }
	}
	
	/**
	 * check if all plugins are activated
	 */
	public function plugins_not_activated()
	{
	    $this->add_flash_notice( __( 'Please install WooCommerce and Germanized first.', 'wcustom' ), "error", false );
	    add_action( 'admin_notices', [$this, 'display_flash_notices'], 12 );
	}
	
	# get credentials
	public function connect_fs()
	{
	    global $wp_filesystem;
	    
	    if( false === ($credentials = request_filesystem_credentials('')) )
	    {
	        return false;
	    }
	    
	    //check if credentials are correct or not.
	    if(!WP_Filesystem($credentials))
	    {
	        request_filesystem_credentials('');
	        return false;
	    }
	    
	    return true;
	}
}