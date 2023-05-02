<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 * @copyright © 2022 Daan van den Bergh
 * @license   BY-NC-ND-4.0
 *            http://creativecommons.org/licenses/by-nc-nd/4.0/
 */
class OmgfPro_Optimize
{
    /**
     * E.g. Material Symbols Outlined and Material Icons include a generic CSS class in the stylesheet, for ease of use
     * and to apply it in your design. These need to be added to the font object, as well, and processed later on.
     * 
     * @since v3.6.5
     * 
     * @param mixed $fonts 
     * @param mixed $url
     *  
     * @return array
     */
    public function additional_css($fonts, $url)
    {
        $response   = wp_remote_get($url, [
            'user-agent' => OMGF_Optimize::USER_AGENT['woff2']
        ]);
        $stylesheet = wp_remote_retrieve_body($response);

        foreach ($fonts as &$font) {
            /**
             * Look for any defined classes in the stylesheet.
             */
            preg_match_all("/\.[a-z\-]+?\s+?{.*?}/s", $stylesheet, $css);

            if (!isset($css[0]) || empty($css[0])) {
                continue;
            }

            $font->additional_css = $css[0];
        }

        return $fonts;
    }
}
