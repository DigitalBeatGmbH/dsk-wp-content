<?php

namespace Wcustom\Wdm\Dropshipping;

use Wcustom\Wdm\Helper;
use \Curl\Curl as PHPCurl;

/**
 * The curl specific functionality of the plugin.
 *
 * @package    Wcustom
 * @subpackage Wcustom/dropshipping
 * @author     WebZap
 */
class Curl extends PHPCurl
{
    
    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($url)
    {
        parent::__construct(WCDM_URL);

        $this->setBasicAuthentication(str_replace(['https://', 'http'], ['', ''], get_option('acf-options-custom_yoururl')), get_option('acf-options-custom_token'));
        $this->get(rtrim(WCDM_URL, '/') . '/' . $url);
    }
}