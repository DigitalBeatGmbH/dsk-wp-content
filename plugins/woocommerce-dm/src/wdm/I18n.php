<?php

namespace Wcustom\Wdm;

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
class I18n
{

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.0.1
	 */
	public function load_plugin_textdomain()
	{
	    load_plugin_textdomain(
			'wcustom',
	        false,
			'/woocommerce-dm/languages'
		);

	}

    /**
     * @param unknown $price
     * @param $useDecimal boolean
     * @return string
     */
    public static function number_format($price, $useDecimal = true)
    {
        switch(ICL_LANGUAGE_CODE) {
            case 'en':
                if ($useDecimal) {
                    return number_format($price, 2);
                } else {
                    return number_format($price, 0);
                }
                break;
            default:
                if ($useDecimal) {
                    return number_format($price, 2, ',', '.');
                } else {
                    return number_format($price, 0, ',', '.');
                }
                break;
        }
    }


}
