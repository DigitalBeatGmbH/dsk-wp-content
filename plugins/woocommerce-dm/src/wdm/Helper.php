<?php

namespace Wcustom\Wdm;

use Wcustom\Wdm\User\User;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.0.1
 * @package    Wcustom
 * @subpackage Wcustom/includes
 * @author     WebZap
 */
class Helper
{
    /**
     * @param string $var
     * @param string $filter
     * @return mixed|NULL
     */
    public function g($var, $filter = FILTER_SANITIZE_STRING, $default = null) {
        if(isset($_GET[$var]) && $_GET[$var] !== 'null') {
            $v = filter_var($_GET[$var], $filter);
            return $v === false && $_GET[$var] !== false ? $default : $v;
        }
        return $default;
    }
    
    /**
     * @param string $var
     * @param string $filter
     * @return mixed|NULL
     */
    public function c($var, $filter = FILTER_SANITIZE_STRING, $default = null) {
        if(isset($_COOKIE[$var]) && $_COOKIE[$var] !== 'null') {
            $v = filter_var($_COOKIE[$var], $filter);
            return $v === false && $_COOKIE[$var] !== false ? $default : $v;
        }
        return $default;
    }
    
    /**
     * @param string $var
     * @param string $filter
     * @return mixed|NULL
     */
    public function p($var, $filter = FILTER_SANITIZE_STRING, $default = null) {
        if(isset($_POST[$var]) && $_POST[$var] !== 'null') {
            $v = filter_var($_POST[$var], $filter);
            return $v === false && $_POST[$var] !== false ? $default : $v;
        }
        return $default;
    }
    
    /**
     * To filter POST vars which are arrays
     * 
     * @param array $var
     * @param string $filter
     * @return array|NULL
     */
    public function p_array($var, $filter = FILTER_SANITIZE_STRING, $default = null) {
        if(isset($_POST[$var]) && $_POST[$var] !== 'null') {
            $arr = [];
            foreach($_POST[$var] as $k => $v) {
                $fv = filter_var($v, $filter);
                $arr[$k] = $fv === false && $v !== false ? $default : $fv;
            }
            return $arr;
        }
        return $default;
    }
    
    /**
     * checks if current user can access this page (based on role)
     * check the return value for !== true
     * 
     * @param string $type
     * @param boolean $redirect
     * @return boolean|string
     */
    public function check_access($type = 'customer', $redirect = true)
    {
        $redirect_url = User::custom_cuar_change_default_customer_page_url('');
        
        if( is_user_logged_in() ) {
            $user = wp_get_current_user();
            if ( in_array( $type, (array) $user->roles ) ) {
                return true;
            }
        }
        
        if($redirect) {
            if(headers_sent()) {
                $string = '<script type="text/javascript">';
                $string .= 'window.location.href = "' . $redirect_url . '"';
                $string .= '</script>';
                
                return $string;
            } else {
                wp_redirect( $redirect_url );
                exit;
            }
        } else {
            return false;
        }
    }
    
    /**
     * splits and associative array into two by given key
     *
     * @param array $arr
     * @param string $skey
     * @return array
     */
    public static function split_menu_array($arr, $skey)
    {
        $arr1 = [];
        $arr2 = [];
        
        $found = false;
        foreach($arr as $key => $val){
            if($found || $key == $skey) {
                $found = true;
                $arr2[$key] = $val;
                continue;
            } else {
                $arr1[$key] = $val;
            }
        }
        
        return [$arr1, $arr2];
    }
    
    
    /**
     * Shorten a text to submitted length with dots
     *
     * @since    0.0.1
     */
    public function text_shortener( $text, $length=50 )
    {
        return strlen( $text ) > $length ? substr( $text, 0, $length) . "..." : $text;
    }
    
    /**
     * Get Template
     *
     * @since    0.0.1
     */
    public function get_template( $template )
    {
        return $this->template_selector( $template );
    }
    
    /**
     * Get Email Template
     *
     * @since    0.0.1
     */
    public function get_mail_template( $template )
    {
        return $this->template_selector( 'mails/' . $template );
    }
    
    /**
     * Get Partial
     *
     * @since    0.0.1
     */
    public function get_partial( $partial, $admin = false, $user = false )
    {
        return $this->template_selector( 'partials/' . $partial, $admin, $user );
    }
    
    /**
     * Template Selector
     *
     * Checks if a custom template exists in the theme folder, if not, load the plugin template file
     *
     * @since    0.0.1
     */
    public function template_selector( $template, $admin = false, $user = false )
    {
        
        // Get the template slug
        // $template_slug = rtrim( $template, '.php' ); - fails when name has p
        if( ! strrpos( $template, ".php" ) ) {
            $template = $template . '.php';
        }
        
        if ( $theme_file = locate_template( $template ) ) {
            $file = $theme_file;
        } else {
            if($admin) {
                $file = plugin_dir_path( dirname( __FILE__ ) ) . 'wdm/Admin/templates/' . $template;
            } elseif($user) {
                $file = plugin_dir_path( dirname( __FILE__ ) ) . 'wdm/User/templates/' . $template;
            } else {
                $file = plugin_dir_path( dirname( __FILE__ ) ) . 'wdm/Visitor/templates/' . $template;
            }
        }
        
        return $file;
    }
	
	/**
	 * Get an HTML img element representing an image attachment
	 *
	 * returns wp_get_attachment_image without a srcset
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|array $size          (Optional)
	 * @param bool         $icon          (Optional)
	 * @param string|array $attr          (Optional)
	 * @return string      HTML img element or empty string.
	 */
	public static function wp_get_attachment_image_no_srcset($attachment_id, $size = 'thumbnail', $icon = false, $attr = '')
	{
		// add a filter to return null for srcset
		add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
		// get the srcset-less img html
		$html = wp_get_attachment_image($attachment_id, $size, $icon, $attr);
		// remove the above filter
		remove_filter( 'wp_calculate_image_srcset_meta', '__return_null' );
		return $html;
	}

}
