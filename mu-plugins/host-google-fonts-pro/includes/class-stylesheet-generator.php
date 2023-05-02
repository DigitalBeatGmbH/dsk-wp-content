<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *           
 * @copyright © 2022 Daan van den Bergh
 * @license   BY-NC-ND-4.0
 *            http://creativecommons.org/licenses/by-nc-nd/4.0/
 */
class OmgfPro_StylesheetGenerator
{
    /**
     * Appends any additional CSS defined in the $fonts object to $stylesheet.
     * 
     * @since v3.6.5 
     * 
     * @param string $stylesheet A valid CSS stylesheet. 
     * @param object $fonts      A structured object containing all fonts found in $stylesheet.
     *  
     * @return string
     */
    public function append_additional_css($stylesheet, $fonts)
    {
        foreach ($fonts as $properties) {
            if (!isset($properties->additional_css)) {
                continue;
            }

            foreach ($properties->additional_css as $css) {
                $stylesheet .= "\n$css";
            }
        }

        return $stylesheet;
    }
}
