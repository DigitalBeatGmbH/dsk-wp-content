<?php
defined('ABSPATH') || exit;

/**
 * @package   OMGF Pro
 * @author    Daan van den Bergh
 *            https://daan.dev
 * @copyright © 2022 Daan van den Bergh
 * @license   BY-NC-ND-4.0
 *            http://creativecommons.org/licenses/by-nc-nd/4.0/
 */
class OmgfPro_Frontend_Optimize
{
    const WEBFONTLOADER_ASYNC_SCRIPT  = "WebFontConfig = { custom: { families: [ '%s' ], urls: [ '%s' ] } };";
    const WEBFONTLOADER_SCRIPT        = "WebFont.load({ custom: { families: [ '%s' ], urls: [ '%s' ] } });";
    const FONT_DISPLAY_ATTRIBUTE      = 'font-display: %s;';
    const JS_WITHOUT_SEMICOLON_THEMES = [
        'jupiter'
    ];

    /** 
     * @since v3.3.0 Contains an array of processed stylesheets to speed up processing on pageload.
     * 
     * @var array $processed_local_stylesheets 
     */
    private $processed_local_stylesheets = [];

    /**
     * @since v3.6.2 Provides an easy interface to add additional handles used by themes/plugins to generate dynamic CSS.
     * 
     * @filter omgf_pro_optimize_dynamic_css_handles
     * 
     * @var array $dynamic_css_handles
     */
    private $dynamic_css_handles = [];

    /**
     * Build Class.
     */
    public function __construct()
    {
        $this->processed_local_stylesheets = get_option(OmgfPro_Admin_Settings::OMGF_PRO_PROCESSED_STYLESHEETS, []) ?: [];
        // TODO: [OP-65] Write documentation about how to use this handle.
        $this->dynamic_css_handles         = apply_filters('omgf_pro_optimize_dynamic_css_handles', ['action', 'custom-css']);

        $this->init();
    }

    /**
     * Actions & Hooks
     * 
     * @return void 
     */
    private function init()
    {
        add_filter('omgf_processed_html', [$this, 'process'], 10, 2);
        add_action('omgf_frontend_process_before_ob_start', [$this, 'maybe_init']);

        /** Material Icons compatibility */
        add_filter('omgf_used_subsets', [$this, 'add_material_icons_subset']);

        /** Early Access compatibility */
        add_filter('omgf_optimize_fonts_object_variants', [$this, 'process_early_access_variants'], 10, 4);
        add_filter('omgf_optimize_parse_variants_regex', [$this, 'modify_early_access_regex'], 10, 2);

        /** Compatibility with themes who use shorthand javascript */
        add_filter('omgf_pro_webfont_loader_async_regex', [$this, 'use_webfontloader_regex_without_semicolon']);
    }

    /**
     * All listed methods are hooked to general WordPress actions/filters, which should only be triggered if OMGF 
     * is actually allowed to run.
     * 
     * @since v3.6.6
     * 
     * @filter omgf_frontend_process_before_ob_start
     * 
     * @return void 
     */
    public function maybe_init()
    {
        // Block Async Google Fonts
        add_action('wp_head', [$this, 'block_async_google_fonts'], 1);

        /** Jupiter Theme compatibility */
        add_action('wp_enqueue_scripts', [$this, 'jupiter_theme_compatibility'], 2);
    }

    /**
     * Process each Advanced Processing option.
     * 
     * @param string                $html 
     * @param OMGF_Frontend_Process $processor
     * 
     * @return string
     *  
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    public function process($html, $processor)
    {
        if (
            OMGF_PRO_PROCESS_INLINE_STYLES
            || !empty(OMGF_PRO_FALLBACK_FONT_STACK)
            || OMGF_PRO_FORCE_FONT_DISPLAY
        ) {
            $html = $this->process_inline_styles($html);
        }

        if (
            OMGF_PRO_PROCESS_LOCAL_STYLESHEETS
            || !empty(OMGF_PRO_FALLBACK_FONT_STACK)
            || OMGF_PRO_FORCE_FONT_DISPLAY
        ) {
            $html = $this->process_local_stylesheets($html);
        }

        if (OMGF_PRO_PROCESS_WEBFONT_LOADER) {
            $html = $this->process_webfont_loader($html);
        }

        if (OMGF_PRO_PROCESS_EARLY_ACCESS) {
            $html = $this->process_early_access($html, $processor);
        }

        $html = $this->process_material_icons($html, $processor);

        return $html;
    }

    /**
     * The Material Icons stylesheet defines a subset, named 'fallback', so we're adding it here.
     * 
     * @param array $subsets 
     * 
     * @return array 
     */
    public function add_material_icons_subset($subsets)
    {
        return array_merge(['fallback'], $subsets);
    }

    /**
     * Modify the detection regex to be compatible with Early Access stylesheets.
     * 
     * @param string $regex 
     * @param string $url 
     * 
     * @return string 
     */
    public function modify_early_access_regex($regex, $url)
    {
        if (!$this->is_early_access($url)) {
            return $regex;
        }

        return '/@font-face\s{.+?}/s';
    }

    /**
     * Source needs to be parsed using a different method.
     * 
     * @param stdClass $font_object 
     * @param string   $stylesheet 
     * @param string   $font_family 
     * @param string   $url 
     * 
     * @return stdClass
     */
    public function process_early_access_variants($font_object, $stylesheet, $font_family, $url)
    {
        if (!$this->is_early_access($url)) {
            return $font_object;
        }

        preg_match_all('/@font-face\s{.+?}/s', $stylesheet, $font_faces);

        if (!isset($font_faces[0]) || empty($font_faces)) {
            return $font_object;
        }

        $src   = [];

        foreach ($font_object as &$font) {
            foreach ($font_faces[0] as $font_face) {
                if (strpos($font_face, $font->fontWeight) !== false && strpos($font_face, $font->fontStyle) !== false) {
                    // Current font face contains the correct src url. Let's grab it!
                    preg_match('/url\((?P<src>.+woff2)\)/', $font_face, $src);

                    break;
                }
            }

            if (!empty($src) && isset($src['src'])) {
                $font->woff2 = $src['src'];
            }
        }


        return $font_object;
    }

    /**
     * Checks if current $url is an Early Access URL.
     * 
     * @param string $url
     *  
     * @return bool 
     */
    private function is_early_access($url)
    {
        return strpos($url, 'earlyaccess') !== false;
    }

    /**
     * Takes care of processing Inline Style blocks containing @import (fonts.googleapis.com) and 
     * @font-face (fonts.gstatic.com) statements.
     * 
     * @param string $html 
     * 
     * @return string
     *  
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function process_inline_styles($html)
    {
        $html = $this->process_imports($html);
        $html = $this->process_font_faces($html);

        return $html;
    }

    /**
     * Replaces all Google Fonts stylesheets found in @import statements with local copies.
     * 
     * @param string $contents A string of either valid HTML or CSS.
     * 
     * @return string 
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function process_imports($contents, $handle_base = 'inline-import')
    {
        /**
         * @since v3.6.6 This matches with SSL, non-SSL and protocol relative references to fonts.googleapis.com and 
         *               @import URL (url()) and @import String syntax.
         */
        preg_match_all('/@import[\s]+?(url\()?["\'](?P<urls>(https:|http:)?\/\/fonts\.googleapis\.com\/.+?)["\'](\))?;/s', $contents, $imports);

        if (!isset($imports['urls']) || empty($imports['urls'])) {
            return $contents;
        }

        $search_replace = $this->build_import_search_replace($imports[0], $imports['urls'], $handle_base);

        return str_replace($search_replace['search'], $search_replace['replace'], $contents);
    }

    /**
     * Build a processable Search/Replace array from @import statements.
     * 
     * @param array $full_matches Array of full matches.
     * @param array $url_matches  Array of partial matches.
     * @param string $handle      The stylesheet handle.
     * 
     * @return array 
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function build_import_search_replace($full_matches, $url_matches, $handle_base = 'inline-import')
    {
        $search  = [];
        $replace = [];

        foreach ($url_matches as $i => $url) {
            /**
             * @since v3.6.0 Use string length to generate a unique-ish identifier for @import-ed stylesheets.
             */
            $original_handle = "$handle_base-" . strlen($url);
            $handle          = OMGF::get_cache_key($original_handle) ?: $original_handle;
            $cached_file     = OMGF_UPLOAD_DIR . "/$handle/$handle.css";

            /**
             * @since v3.6.0 Check if stylesheet is marked for unloading, before we do anything else.
             */
            if (in_array($original_handle, OMGF::unloaded_stylesheets())) {
                $search[$i]  = $full_matches[$i];
                $replace[$i] = '';

                continue;
            }

            /**
             * If file is already cached, and omgf_optimize parameter isn't set, let's just assume we don't need
             * to re-optimize.
             */
            if (!isset($_GET['omgf_optimize']) && file_exists($cached_file)) {
                $search[$i]  = $url;
                $replace[$i] = str_replace(OMGF_UPLOAD_DIR, OMGF_UPLOAD_URL, $cached_file);

                continue;
            }

            $cache_dir = $this->run_optimize($url, $handle, $original_handle);

            if (!$cache_dir) {
                continue;
            }

            $search[$i]  = $url;
            $replace[$i] = $cache_dir;
        }

        return ['search' => $search, 'replace' => $replace];
    }

    /**
     * Process inline @font-face statements.
     * 
     * @param string $contents    A string of either valid HTML or CSS.
     * @param string $base_handle Default: 'inline-font-face'
     * 
     * @return string
     *  
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function process_font_faces($contents, $base_handle = 'inline-font-face')
    {
        /**
         * TODO: [OP-52] Figure out a regex (without catastrophic backtracking) which only retrieves @font-face
         *       statements containing fonts.gstatic.com.
         */
        preg_match_all('/@font-face[\s]*?{.*?}/si', $contents, $font_faces);

        // No @font-face statements found containing Google Fonts.
        if (!isset($font_faces[0]) || empty($font_faces[0])) {
            return $contents;
        }

        $font_faces[0] = array_filter($font_faces[0], function ($value) {
            return strpos($value, 'fonts.gstatic.com') !== false;
        });

        if (empty($font_faces[0])) {
            return $contents;
        }

        $families       = $this->convert_font_faces($font_faces[0]);
        $search_replace = $this->build_font_face_search_replace($font_faces[0], $families, $base_handle);

        /**
         * @var array|string $search Because $search can be either an array or string, we loop through 
         *                           $search_replace to make sure all entries are replaced with a single
         *                           entry from $search_replace['replace'].
         * 
         *                           This was introduced to add compatibility for Page Builders like 
         *                           like Themify which load all subsets using @font-face statements.
         * 
         * @see self::build_font_face_search_replace()
         * 
         * @since v3.4.6
         */
        foreach ($search_replace['search'] as $find_key => $search) {
            $contents = str_replace($search, $search_replace['replace'][$find_key], $contents);
        }

        if (!empty(OMGF_PRO_FALLBACK_FONT_STACK)) {
            $contents = $this->process_fallback_font_stacks($contents);
        }

        if (OMGF_PRO_FORCE_FONT_DISPLAY) {
            $contents = $this->process_force_font_display($contents);
        }

        return $contents;
    }

    /**
     * Converts @font-face statements to Array. If a unicode range is defined, 
     * it will map it to an identifier, e.g. "latin-ext"
     * 
     * @param array $font_faces
     *  
     * @return array [ font_family ] [ variants => [ variant_id => font_src ] , subsets ]
     */
    private function convert_font_faces($font_faces)
    {
        $families = [];

        foreach ($font_faces as $i => $font_face) {
            preg_match('/font-family:[\s]*[\'"]?(?P<font_family>.*?)[\'"]?;/', $font_face, $font_family);
            preg_match('/font-style:[\s]*[\'"]?(?P<font_style>.*?)[\'"]?;/', $font_face, $font_style);
            preg_match('/font-weight:[\s]*[\'"]?(?P<font_weight>.*?)[\'"]?;/', $font_face, $font_weight);
            preg_match('/src:.*?url\([\'"]?(?P<url>.*?)[\'"]?\)/', $font_face, $font_src);
            preg_match('/unicode-range:[\s]*(?P<range>.*?)?;/', $font_face, $range);

            $font_family = $font_family['font_family'] ?? '';
            $font_style  = isset($font_style['font_style']) && $font_style['font_style'] != 'normal' ? $font_style['font_style'] : '';
            $font_weight = $font_weight['font_weight'] ?? '400';
            $font_src    = $font_src['url'] ?? '';
            $range       = $range['range'] ?? '';
            $subset      = array_search(str_replace(' ', '', $range), OmgfPro_UnicodeRanges::MAP);

            /**
             * @since v3.4.5 If this variant (font-weight + font-style) has already been added before,
             *               this is most likely a subset variation. Instead of overwriting the previously
             *               added a value, create a comma-separated list of different URLs.
             * 
             *               This was added to include compatibility for page builders like Themify, which
             *               like to add every subset variation of the same font style as a @font-face 
             *               statement. Because, why not? Why use Google Fonts' API if you can generate 
             *               your own bloated CSS File?
             */
            if (!isset($families[$font_family]['variants'][(string) $font_weight . $font_style])) {
                $families[$font_family]['variants'][(string) $font_weight . $font_style] = $font_src;
            } else {
                $families[$font_family]['variants'][(string) $font_weight . $font_style] .= ',' . $font_src;
            }

            if ($subset && !in_array($subset, $families[$font_family]['subsets'][$i] ?? [])) {
                $families[$font_family]['subsets'][$i] = $subset;
            }
        }

        return $families;
    }

    /**
     * Build a Search/Replace array from converted font faces.
     * 
     * @param array  $font_faces   Array of full regex matched @font-face statements. Used for unloading.
     * @param array  $families     Array of detected font-families.
     * @param string $base_handle Default: 'inline-font-face'
     * 
     * @return array 
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function build_font_face_search_replace($font_faces, $families, $base_handle = 'inline-font-face')
    {
        $search   = [];
        $replace  = [];

        foreach ($families as $font_family => $properties) {
            $family          = $font_family . ':' . implode(',', array_keys($properties['variants']));
            $original_handle = "$base_handle-" . str_replace(' ', '-', strtolower($font_family));
            $font_id         = str_replace(' ', '-', strtolower($font_family));;

            /**
             * @since v3.3.0 There's no need to proceed if the entire stylesheet is marked for unloading.
             * 
             * BUG: [OP-63] If two local stylesheets contain the same @font-face statements, checking unload for one, unloads the other as well.
             */
            if (in_array($original_handle, OMGF::unloaded_stylesheets())) {
                foreach ($font_faces as $font_face) {
                    $search[]  = $font_face;
                    $replace[] = '';
                }

                // Skip to the next stylesheet handle.
                continue;
            }

            /**
             * @since v3.3.0 Do a quick check to see which @font-face statements should be removed entirely.
             */
            foreach ($properties['variants'] as $variant => $external_url) {
                // The stylesheet has no relevant fonts marked for unloading.
                if (!isset(OMGF::unloaded_fonts()[$original_handle][$font_id])) {
                    break;
                }

                if (in_array($variant, OMGF::unloaded_fonts()[$original_handle][$font_id])) {
                    /** Fetch the string to be removed from the stylesheet. */
                    $to_unload = array_filter($font_faces, function ($value) use ($external_url) {
                        return strpos($value, $external_url) !== false;
                    });

                    if (!reset($to_unload)) {
                        OMGF::debug(__('No @font-face matched.', 'omgf-pro'));

                        continue;
                    }

                    // Get the first element, because duplicate matches are practically impossible.
                    $search[]  = reset($to_unload);
                    $replace[] = '';
                }
            }

            $handle = OMGF::get_cache_key($original_handle) ?: $original_handle;
            /**
             * Whether the optimization bails early depends on the presence of the $_GET parameter.
             */
            $fonts = $this->run_optimize('https://fonts.googleapis.com/css?family=' . $family, $handle, $original_handle, 'object', !isset($_GET['omgf_optimize']));

            if (!$fonts) {
                continue;
            }

            /**
             * @since v3.3.0 $fonts has already filtered unloaded fonts. If the stylesheet is marked for unloading i.e. no
             *               fonts are loaded, it'll skip the foreach() entirely.
             */
            foreach ($fonts as $handle => $contents) {
                // TODO: [OP-57] This shouldn't be necessary, if OMGF_Optimize just returns a proper object.
                $contents = reset($contents);

                foreach ($contents->variants as $variant) {
                    $variant->id = $variant->id == 'regular' ? '400' : ($variant->id == 'italic' ? '400italic' : $variant->id);

                    /**
                     * If variant doesn't exist, skip.
                     */
                    if (!isset($properties['variants'][$variant->id])) {
                        continue;
                    }

                    /**
                     * @since v3.4.5 $ext_url can be a comma-separated list of external URLs, which are
                     *               added to $search[] as a sub array for further processing later on.
                     * 
                     *               This was added to include compatibility for Page Builders like Themify
                     *               which load all seperate subsets of a font style using @font-face
                     *               statements. Because that's a perfectly sensible thing to do. I mean,
                     *               it's not like Google Fonts has an API for that... :-/
                     * 
                     * @see self::convert_font_faces()
                     */
                    $ext_url   = $properties['variants'][$variant->id];
                    $search[]  = strpos($ext_url, ',') !== false ? array_unique(explode(',', $ext_url)) : $ext_url;

                    /**
                     * @since v3.6.0 Always force WOFF2. If people start demanding WOFF or TTF, I'll think of another solution, but
                     *               WOFF2 should suffice in >95% situations.
                     * 
                     * BUG: [OP-59] If original @font-face statement contained a .WOFF file, the syntax is off. 
                     */
                    $replace[] = isset($variant->woff2) ? urldecode($variant->woff2) : $ext_url;
                }
            }
        }

        return ['search' => $search, 'replace' => $replace];
    }

    /**
     * Try-catch wrapper for the OMGF_Optimize class.
     * 
     * @param mixed $url             Full Google Fonts API request, e.g. https://fonts.googleapis.com/css?family=Open+Sans:100,200,300,etc.
     * @param mixed $cache_handle    Cache handle used for storing the fonts and generated stylesheet in OMGF's cache directory.
     * @param mixed $original_handle Original cache handle (usually the stylesheet's ID)
     * @param mixed $to_return 
     * 
     * @return string|array 
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function run_optimize($url, $cache_handle, $original_handle, $to_return = 'url', $return_early = false)
    {
        /**
         * @todo Should we still force subsets?
         */
        // $subset = $this->force_subset();

        try {
            $optimize = new OMGF_Optimize($url, $cache_handle, $original_handle, $to_return, $return_early);
            $fonts    = $optimize->process();
        } catch (Requests_Exception $e) {
            OmgfPro_Admin_Notice::set_notice($e);
        }

        if (is_wp_error($fonts)) {
            /** @var WP_Error $fonts */
            OmgfPro_Admin_Notice::set_notice(__('Something went wrong while trying to fetch ', 'omgf-pro') . ' - ' . $fonts->get_error_code() . ': ' . $fonts->get_error_message(), 'error', 'omgf-pro-optimization-failed');
        }

        return $fonts;
    }

    /**
     * Check for @import and @font-face statements inside local stylesheets.
     * Rewrite them to use local copies and cache them.
     * 
     * @param string $html 
     * 
     * @return string 
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function process_local_stylesheets($html)
    {
        $search   = [];
        $replace  = [];

        /**
         * @since v3.4.5 Use lookaround for stricter matches.
         */
        preg_match_all('/(?=\<link).+?href=[\'"](?P<urls>.+?)[\'"].+?(?<=>)/', $html, $links);

        if (!isset($links['urls']) || empty($links['urls'])) {
            return $html;
        }

        foreach ($links['urls'] as $i => $url) {
            /**
             * Check if full match is a stylesheet.
             */
            if (!preg_match('/rel=[\'"]stylesheet[\'"]/', $links[0][$i])) {

                continue;
            }

            /**
             * There's no need to check inside either core or already processed stylesheets.
             */
            if (
                (strpos($url, get_home_url()) === false
                    && strpos($url, '/') !== 0)
                || strpos($url, 'wp-includes') !== false
                || strpos($url, OMGF_UPLOAD_URL) !== false
            ) {
                continue;
            }

            /**
             * Fix for protocol relative URLs.
             */
            $fixed_url = $url;

            if (strpos($url, '//') === 0) {
                $fixed_url = (is_ssl() ? 'https:' : 'http:') . $url;
            }

            /**
             * @since v3.6.0 Check if URL is relative and fix it.
             */
            if (preg_match('/^\/[a-zA-Z0-9]+/', $fixed_url) === 1) {
                $fixed_url = get_home_url() . $fixed_url;
            }

            /**
             * To avoid collisions on servers that have allow_url_fopen disabled, we fetch the
             * contents using absolute paths. 
             * 
             * @since v3.4.4 using content_url is warranted, because we need to insert the path to OMGF (Pro)'s upload dir.
             * @since v3.6.0 Use a preg_replace() to remove any present query parameters from local paths ($local_path and $cache_path)
             *               Query parameters are allowed in URLs.
             */
            $content_url = $this->get_content_url();
            $local_path  = preg_replace('/\?.*$/', '', str_replace($content_url, WP_CONTENT_DIR, $fixed_url));
            $cache_path  = preg_replace('/\?.*$/', '', str_replace($content_url, OMGF_UPLOAD_DIR, $fixed_url));
            $cache_url   = str_replace($content_url, OMGF_UPLOAD_URL, $fixed_url);

            /**
             * @since v3.3.2 Add compatibility for themes/plugins using dynamic CSS generator scripts.
             */
            if (strpos($fixed_url, '?') !== false) {
                $path  = parse_url($fixed_url, PHP_URL_PATH);
                $query = parse_url($fixed_url, PHP_URL_QUERY);

                foreach ($this->dynamic_css_handles as $handle) {
                    if (strpos($query, $handle) !== false && !$this->ends_with($path, '.css')) {
                        /**
                         * If this is a dynamic CSS generator, reset $local_path to URL, because we can't convert that to a local path.
                         */
                        $local_path = $fixed_url;
                        $cache_path = $this->convert_dynamic_css($fixed_url, $handle);
                        $cache_url  = $this->convert_dynamic_css($fixed_url, $handle, 'url');
                    }
                }
            }

            /**
             * @since v3.6.2 If we failed to generate a proper cache path, after all our tricks. Let's just bail.
             *               Prevents "HTTP wrapper does not support writeable connections" errors.
             */
            if (strpos($cache_path, 'http') === 0) {
                continue;
            }

            /**
             * If this stylesheet has been cached previously, assume nothing has changed and continue to the next.
             */
            if (!isset($_GET['omgf_optimize']) && file_exists($cache_path) && in_array($url, $this->processed_local_stylesheets)) {
                $search[$i]  = $url;
                $replace[$i] = $cache_url;

                continue;
            }

            /**
             * @since v3.6.0 If file wasn't cached, but origin file does exist, and it was processed before, bail!
             */
            if (!isset($_GET['omgf_optimize']) && !file_exists($cache_path) && file_exists($local_path) && in_array($url, $this->processed_local_stylesheets)) {
                continue;
            }

            // Get rid of any query parameters.
            if (strpos($local_path, '?')) {
                $parsed_url = parse_url($local_path);
               
                if (strpos($parsed_url['query'], 'action') === false) {
                    $local_path = $parsed_url['path'];
                }
                
            }

             /**
                 * 
                 * 
                 * JANBUCHWALD
                 * 
                 * 
                 */
                error_log($_SERVER['HTTP_HOST']. " " .$local_path);

            $contents   = '';
            $comparison = '';

            /**
             * @since v3.4.6 Some themes/plugins insert non-existent files in the HTML, so let's check
             *               if it exists first, before attempting to fetch the contents.
             * 
             * @since v3.6.0 URLs will always be fetched.
             * 
             */
            if (strpos($local_path, 'http') === 0 || @file_exists($local_path)) {
                $contents   = file_get_contents($local_path);
                $comparison = $contents;
            }

            /**
             * @since v3.6.0 Mark it as processed, before going thru it all.
             */
            if (!in_array($url, $this->processed_local_stylesheets)) {
                $this->processed_local_stylesheets[] = $url;
            }


             /**
                 * 
                 * 
                 * JANBUCHWALD
                 * 
                 * 
                 */
                error_log($_SERVER['HTTP_HOST']. " URL " .$url);

            if (!$contents) {
                continue;
            }

            if (OMGF_PRO_PROCESS_LOCAL_STYLESHEETS) {
                // TODO: [OP-62] Add user friendly handles (including stylesheet ID, if any) + migration script.
                $contents = $this->process_imports($contents, 'local-stylesheet-import');
                $contents = $this->process_font_faces($contents, 'local-stylesheet-font-face');
            }

            if (!empty(OMGF_PRO_FALLBACK_FONT_STACK)) {
                $contents = $this->process_fallback_font_stacks($contents);
            }

            if (OMGF_PRO_FORCE_FONT_DISPLAY) {
                $contents = $this->process_force_font_display($contents);
            }

            /**
             * No need to cache it, if we didn't change anything.
             */
            if ($comparison === $contents) {
                continue;
            }

            /**
             * Now we're sure that contents in the stylesheet has changed and it needs to be cached,
             * convert relative to absolute URLs (if needed)
             */
            $contents = $this->convert_rel_to_abs_url($contents, $fixed_url);

            if (strpos($cache_path, '?') !== false) {
                $cache_path = parse_url($cache_path, PHP_URL_PATH);
            }

            $filename  = basename($cache_path);
            $cache_dir = str_replace($filename, '', $cache_path);

            if (!file_exists(str_replace($filename, '', $cache_path))) {
                wp_mkdir_p($cache_dir);
            }

            $write = file_put_contents($cache_path, $contents);

            if (!$write) {
                // TODO: [OP-53] Examine if it's needed to throw an error if writing contents failed.
                continue;
            }

            $search[$i]  = $url;
            $replace[$i] = $cache_url;
        }
        /**
         * 
         *  JANBUCHWALD
         * 
         * 
         */
        error_log($_SERVER['HTTP_HOST']. " " .count($this->processed_local_stylesheets));
        update_option(OmgfPro_Admin_Settings::OMGF_PRO_PROCESSED_STYLESHEETS, $this->processed_local_stylesheets);

        if (empty($search) || empty($replace)) {
            return $html;
        }

        return str_replace($search, $replace, $html);
    }

    /**
     * Provides compatibility fixes for 3rd party (CDN) plugins, i.e.
     * plugins which alter the WordPress URL.
     * 
     * Uses content_url() to determine the correct wp-content directory. Makes any rewrites needed to comply
     * with (supported) 3rd party plugins.
     * 
     * @since v3.4.6 Added compatibility with Bunny CDN.
     * 
     * @return string An absolute URL pointing to the wp-content directory, e.g. https://yoursite.com/wp-content
     */
    private function get_content_url()
    {
        if (defined('BUNNYCDN_PLUGIN_FILE')) {
            $bunny_cdn = get_option('bunnycdn');

            if ($bunny_cdn != false) {
                $cdn_domain_name = $bunny_cdn['cdn_domain_name'] ?? '';

                if ($cdn_domain_name) {
                    $is_ssl = strpos(get_home_url(), 'https://') === 0;

                    return str_replace(get_home_url(), $is_ssl ? 'https://' . $cdn_domain_name : 'http://' . $cdn_domain_name, content_url());
                }
            }
        }

        return content_url();
    }

    /**
     * Check if $string ends with $end.
     * 
     * @param string $string 
     * @param string $end
     *  
     * @return bool 
     */
    private function ends_with($string, $end)
    {
        $len = strlen($end);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $end);
    }

    /**
     * Converts e.g. ./wp-admin/admin.ajax?action=kirky-styles to ./wp-content/uploads/omgf/kirky-styles/kirky-styles.css
     * 
     * @since v3.3.2 Adds (limited) compatibility for themes (e.g. Kirki) and plugins using Dynamic CSS generator scripts.
     * @since v3.6.0 Added $handle parameter, to apply this method with other present params used for dynamic CSS generation, e.g. custom-css
     * 
     * @param string $url    The requested URL.
     * @param string $handle The paramater used for storing the stylesheet on the server.
     * @param string $return path|url
     * 
     * @return string 
     */
    private function convert_dynamic_css($url, $handle = 'action', $return = 'path')
    {
        $query = parse_url(html_entity_decode($url), PHP_URL_QUERY);

        parse_str($query, $params);

        if ($return == 'url') {
            return OMGF_UPLOAD_URL . '/' . $params[$handle] . '/' . $params[$handle] . '.css';
        }

        return OMGF_UPLOAD_DIR . '/' . $params[$handle] . '/' . $params[$handle] . '.css';
    }

    /**
     * Process Fallback Font Stacks in local stylesheets.
     * 
     * @param string $contents Valid HTML/CSS.
     *  
     * @return string 
     */
    private function process_fallback_font_stacks($contents)
    {
        $font_stacks = [];

        foreach (OMGF::optimized_fonts() as $fonts) {
            foreach ($fonts as $font) {
                $font_family = urldecode($font->family);

                preg_match_all("/font-family:[\s]*?(?P<font_stack>['\"]?$font_family.*?);/i", $contents, $matches);

                if (!isset($matches['font_stack']) || empty($matches['font_stack'])) {
                    continue;
                }

                $font_stacks = array_merge($matches['font_stack'], $font_stacks);
            }
        }

        if (empty($font_stacks)) {
            return $contents;
        }

        $search  = [];
        $replace = [];

        foreach ($font_stacks as $font_stack) {
            $current_font_stack = $font_stack;

            // If a fallback is already set, lose it.
            if (strpos($font_stack, ',') !== false) {
                $font_stack = explode(',', $font_stack)[0];
            }

            // Make sure !important statements are stripped, too.
            if (strpos($font_stack, '!important') !== false) {
                $font_stack = str_replace(['!important', ' !important'], '', $font_stack);
            }

            $font_stack = trim($font_stack, '\'"');
            $font_id    = str_replace(' ', '-', strtolower($font_stack));
            $fallback   = $this->load_fallback_font_stack($font_stack, $font_id);

            // No fallback was found. Skip out.
            if (!$fallback) {
                continue;
            }

            $search[$font_id]  = $current_font_stack;
            $replace[$font_id] = $fallback;
        }

        // Rewrite contents of stylesheet to include fallback font stack.
        $contents = str_replace($search, $replace, $contents);

        return $contents;
    }

    /**
     * Map fallback font stack option to actual fallback font stack.
     * 
     * @since v2.5
     * 
     * @param string $font_stack Current font stack in CSS/HTML
     * @param string $font_id    Current font stack's ID in CSS/HTML
     * 
     * @return bool|string 
     */
    public function load_fallback_font_stack($font_stack, $font_id)
    {
        $fallback = '';

        foreach (OMGF_PRO_FALLBACK_FONT_STACK as $font_families) {
            foreach ($font_families as $font_family => $selected_fallback) {
                if ($font_family != $font_id || !$selected_fallback) {
                    continue;
                }

                $fallback = OmgfPro_FallbackFontStacks::MAP[$selected_fallback];

                break;
            }
        }

        if (!$fallback) {
            return false;
        }

        /**
         * @since v3.2.0 If Replace is checked for this font family, then just return $fallback
         *               instead of appending it to $font_family. 
         */
        $replaces = OMGF_PRO_REPLACE_FONT;

        foreach ($replaces as $font_families) {
            if (isset($font_families[$font_id])) {
                return $fallback;
            }
        }

        return $font_stack . ', ' . $fallback;
    }

    /**
     * Convert relative URLS in $contents to Absolute URLs using $url to decide the base.
     * 
     * @param string $contents Valid CSS
     * @param string $url 
     * 
     * @return string Valid CSS
     */
    private function convert_rel_to_abs_url($contents, $url)
    {
        preg_match_all('/url\([\'"]?(?P<src>.*?)["\']?\)/i', $contents, $srcs);

        if (!isset($srcs['src']) || empty($srcs['src'])) {
            return $contents;
        }

        $has_rel_url = false;

        foreach ($srcs['src'] as $src) {
            if ($this->is_rel_url($src)) {
                $has_rel_url = true;

                break;
            }
        }

        if (!$has_rel_url) {
            return $contents;
        }

        OMGF::debug(sprintf(__('Relative URLs found in %s. Rewriting...', 'omgf-pro'), $url));

        return $this->convert_to_abs_url($contents, $url);
    }

    /**
     * Checks if $source contain mentions of '../' or doesn't begin with either 'http' or '../'.
     * 
     * @param string $source 
     * @return bool  false || true for e.g. "../fonts/file.woff2" or "fonts/file.woff2"
     */
    private function is_rel_url(string $source)
    {
        /** 
         * Don't rewrite, if:
         * 
         * @since v3.6.3 this is either a root or protocol relative URL or,
         * @since v3.6.4 if this is a Base64 encoded datatype.
         */
        if (strpos($source, '/') === 0 || strpos($source, 'data:') === 0) {
            return false;
        }

        // true: ../fonts/file.woff2
        return strpos($source, '../') === 0
            // true: fonts/file.woff2
            || (strpos($source, 'http') === false && strpos($source, '../') === false && strpos($source, '/') > 0)
            // true: file.woff2
            || (strpos($source, 'http') === false && strpos($source, '../') === false && strpos($source, '/') === false && preg_match('/^[a-zA-Z]/', $source) === 1);
    }

    /**
     * Convert any relative URLs in $stylesheet to absolute URLs in using $source.
     * 
     * @param string $string 
     * @param string $source 
     * 
     * @return string 
     */
    public function convert_to_abs_url(string $stylesheet, string $source)
    {
        preg_match_all('/url\([\'\"]?(?<rel>.+?)[\'\"]?\)/', $stylesheet, $urls);

        if (!isset($urls['rel']) && empty($urls['rel'])) {
            return $stylesheet;
        }

        $rels = $urls['rel'];
        $replace  = [];

        foreach ($rels as $key => $rel) {
            if (!$this->is_rel_url($rel)) {
                continue;
            }

            OMGF::debug(sprintf(__('Rewriting %s', 'omgf-pro'), $rel));

            $folder_depth  = substr_count($rel, '../');
            $url_to_insert = $source;

            /**
             * Remove everything after the last occurence of a forward slash ('/');
             * 
             * $i = 0: Filename (current directory)
             *      1: First level parent directory, i.e. '../'
             *      2: 2nd level parent directory, i.e. '../../'
             *      3: Etc.
             */
            for ($i = 0; $i <= $folder_depth; $i++) {
                $url_to_insert = substr($source, 0, strrpos($url_to_insert, '/'));
            }

            OMGF::debug(sprintf(__('Rewriting %s to %s', 'omgf-pro'), $rel, $url_to_insert));

            $path          = ltrim($rel, './');
            $search[$key]  = $rel;
            $replace[$key] = $url_to_insert . '/' . $path;
        }

        /**
         * @since v3.4.2 Filter out duplicate values to prevent repeated search-replace madness.
         */
        foreach ($search as $key => $to_search) {
            /**
             * @since v3.5.1 We're using tildes (~) as delimiters, so we don't have to escape slashes in URLs.
             *               Noice, roight? We only need to escape question marks (?), because it's a dumbass
             *               fix for webfonts in Internet Exploder (?#eotfix).
             */
            $to_search  = str_replace('?', '\?', $to_search);
            $stylesheet = preg_replace("~(url\(['\"]?)$to_search(['\"]?\))~si", '$1' . $replace[$key] . '$2', $stylesheet);
        }

        return $stylesheet;
    }

    /**
     * Replaces existing font-display attribute values, and inserts it where its missing.
     * 
     * @param string  $contents Valid CSS/HTML.
     * 
     * @return string valid CSS/HTML
     */
    private function process_force_font_display($contents)
    {
        /**
         * If Font Display attribute is present, somewhere in the document, replace it with the set value.
         * Matches either font-display: swap; and font-display: swap}.
         */
        $contents = preg_replace('/(font-display:[\s]*).*?([;}])/si', '$1' . OMGF_DISPLAY_OPTION . '$2', $contents);

        /**
         * Match all @font-face statements.
         * 
         * TODO: [OP-54] Create a regex to match all @font-face statements NOT containing a font-display attribute.
         */
        preg_match_all('/@font-face[\s]*{(.*?)}/s', $contents, $font_faces);

        if (!isset($font_faces[0]) || empty($font_faces[0])) {
            return $contents;
        }

        $replace = [];
        $search  = [];
        $attr    = sprintf(self::FONT_DISPLAY_ATTRIBUTE, OMGF_DISPLAY_OPTION);

        foreach ($font_faces[0] as $key => $font_face) {
            // If a font-display attribute is already present. Skip it.
            if (strpos($font_face, 'font-display') !== false) {
                continue;
            }

            $search[$key]  = $font_faces[0][$key];
            $replace[$key] = substr_replace($font_faces[0][$key], $attr, strpos($font_faces[0][$key], ';') + 1, 0);
        }

        // No need to continue, if no work was done.
        if (empty($search) || empty($replace)) {
            return $contents;
        }

        // Rewrite contents of stylesheet to include font-display attribute.
        $contents = str_replace($search, $replace, $contents);

        return $contents;
    }

    /**
     * @param string $html Valid HTML
     * 
     * @return string 
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function process_webfont_loader($html)
    {
        /** @var string $local_lib */
        $local_lib = str_replace(['https:', 'http:'], '', plugin_dir_url(OMGF_PRO_PLUGIN_FILE) . 'assets/js/lib/webfont.js');

        // Replace any webfont.js libraries with the one included in OMGF Pro.
        $html = preg_replace('/\/\/.*\/(webfont\.js|webfont\.min\.js)/', $local_lib, $html);

        // Parse script blocks
        preg_match_all('/<script.*?<\/script>/s', $html, $script_blocks);

        if (!isset($script_blocks[0]) || empty($script_blocks[0])) {
            return $html;
        }

        $script_blocks = $script_blocks[0];
        $async_configs = [];
        $rb_configs    = [];

        foreach ($script_blocks as $block) {
            /**
             * Two separate if-statements, because one <script> block can theoritically contain multiple
             * types of Web Font Loaders.
             */
            if (strpos($block, 'WebFontConfig') !== false) {
                $async_configs[] = $block;
            }

            if (strpos($block, 'WebFont.load') !== false) {
                $rb_configs[] = $block;
            }
        }

        if (!empty($async_configs)) {
            $async_configs = $this->build_webfont_loader_search_replace($async_configs, true);
            $html          = str_replace($async_configs['search'], $async_configs['replace'], $html);
        }

        if (!empty($rb_configs)) {
            $rb_configs = $this->build_webfont_loader_search_replace($rb_configs);
            $html       = str_replace($rb_configs['search'], $rb_configs['replace'], $html);
        }

        return $html;
    }

    /**
     * @param mixed $configs 
     * @param bool $async 
     * 
     * @return array 
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function build_webfont_loader_search_replace($configs, $async = false)
    {
        $search  = [];
        $replace = [];

        if ($async) {
            $regex = '/WebFontConfig(?:(?!WebFontConfig).)*?=\s+{(.*?)};/s';
        } else {
            $regex = '/WebFont\.load\(.*?\);/s';
        }

        foreach ($configs as $config) {
            preg_match_all(apply_filters('omgf_pro_web_font_loader_search_replace_regex', $regex, $config), $config, $matches);

            if (!isset($matches[0]) || empty($matches[0])) {
                continue;
            }

            foreach ($matches[0] as $i => $match) {
                // FIXME: [OP-39] When multiple web font loaders are present in separate scripts, stylesheets get mixed up.
                $cache_key  = OMGF::get_cache_key("webfont-loader-$i") ?: "webfont-loader-$i";
                $cache_url  = OMGF_UPLOAD_URL . "/$cache_key/$cache_key.css";
                $cache_path = OMGF_UPLOAD_DIR . "/$cache_key/$cache_key.css";

                /**
                 * If stylesheets is marked as unloaded, remove the entire config.
                 */
                if (OMGF::unloaded_stylesheets() && in_array($cache_key, OMGF::unloaded_stylesheets())) {
                    $search[]  = $match;
                    $replace[] = '';

                    continue;
                }

                $request = $this->convert_webfont_config($match);

                /**
                 * Allow filtering the output that should replace the found match.
                 * 
                 * @since v3.6.6
                 * 
                 * @filter omgf_pro_frontend_web_font_loader_replace_script
                 */
                $replace_script = apply_filters('omgf_pro_frontend_web_font_loader_script', $async ? self::WEBFONTLOADER_ASYNC_SCRIPT : self::WEBFONTLOADER_SCRIPT);

                if (!isset($_GET['omgf_optimize']) && file_exists($cache_path)) {
                    $search[]  = $match;
                    $replace[] = sprintf($replace_script, $request, $cache_url);

                    continue;
                }

                $cache_url = $this->run_optimize('https://fonts.googleapis.com/css?family=' . $request, OMGF::get_cache_key("webfont-loader-$i") ?: "webfont-loader-$i", "webfont-loader-$i");
                $search[]  = $config;
                $replace[] = sprintf($replace_script, $request, $cache_url);
            }
        }

        return ['search' => $search, 'replace' => $replace];
    }

    /**
     * Convert WebFontConfig object to a string.
     * 
     * @param string $config 
     * 
     * @return string Returns a valid family string for use with OMGF_Optimize().
     */
    private function convert_webfont_config($config)
    {
        /**
         * Captures everything between [].
         * This regex is much less prone to error and faster, compared to negative look back, etc.
         */
        preg_match_all('/\[[^\[\]]*\]/', $config, $families);

        if (empty($families)) {
            return [];
        }

        $requested_families = [];

        foreach (reset($families) as $match) {
            if (strpos($match, 'google')) {
                // This is the font object itself. Let's move on...
                continue;
            }

            // If $string contains alphabetic characters, we can assume it's a font-family request.
            $has_letters = preg_match("/[a-z]/i", $match);

            if ($has_letters !== 0 && $has_letters !== false) {
                $requested_families[] = $match;
            }
        }

        $request_string = '';

        foreach ($requested_families as $fonts) {
            $fonts          = trim($fonts, " \n\r\t\v\x00[]'\"");
            $formatted      = preg_replace("/(?:\',\s*\'|\",\s*\")/", '|', $fonts);
            $request_string .= empty($request_string) ? $formatted : '|' . $formatted;
        }

        return $request_string;
    }

    /**
     * Process all Early Access stylesheets.
     * 
     * @param string $html 
     * @param OMGF_Frontend_Process $processor
     * 
     * @return string 
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function process_early_access($html, OMGF_Frontend_Process $processor)
    {
        return $this->invoke_processor($html, $processor, '/<link.*fonts\.googleapis\.com\/earlyaccess.*?[\/]?>/');
    }

    /**
     * Process Material Icons in $html. Multiple occurences are replaced with the same stylesheet.
     * 
     * @param string                $html      Valid HTML
     * @param OMGF_Frontend_Process $processor 
     * 
     * @return string Valid HTML
     * 
     * @throws SodiumException 
     * @throws SodiumException 
     * @throws TypeError 
     * @throws TypeError 
     * @throws TypeError 
     */
    private function process_material_icons($html, OMGF_Frontend_Process $processor)
    {
        return $this->invoke_processor($html, $processor, '/<link.*fonts\.googleapis\.com\/icon.*?[\/]?>/');
    }

    /**
     * 
     */
    private function invoke_processor($html, OMGF_Frontend_Process $processor, $regex)
    {
        preg_match_all($regex, $html, $links);

        if (!isset($links[0]) || empty($links[0])) {
            return $html;
        }

        $google_fonts   = $processor->build_fonts_set($links[0]);
        $search_replace = $processor->build_search_replace($google_fonts);

        foreach ($google_fonts as $google_font) {
            $key = array_search($google_font['href'], $search_replace['search']);

            if ($key !== false) {
                $search_replace['search'][$key] = $google_font['href'];
            }
        }

        if (empty($search_replace['search']) || empty($search_replace['replace'])) {
            return $html;
        }

        return str_replace($search_replace['search'], $search_replace['replace'], $html);
    }

    /**
     * Filters the WebFontLoader regex to not include a semicolon.
     * 
     * @param string $regex A valid regular expression
     * 
     * @return string A valid regular expression
     */
    public function use_webfontloader_regex_without_semicolon($regex)
    {
        if (in_array(wp_get_theme()->template, self::JS_WITHOUT_SEMICOLON_THEMES)) {
            return '/WebFontConfig(?:(?!WebFontConfig).)*?=\s+{(.*?)}.*?}/s';
        }

        return $regex;
    }

    /**
     * Modify Web Font Loader script to make sure OMGF Pro can properly parse it.
     * 
     * @return void 
     */
    public function jupiter_theme_compatibility()
    {
        if (isset($_GET['nomgf'])) {
            return;
        }

        if (wp_get_theme()->template !== 'jupiter') {
            return;
        }

        global $wp_scripts;

        $webfont_loader = $wp_scripts->registered['mk-webfontloader'] ?? '';

        if (!$webfont_loader) {
            return;
        }

        $inline_scripts = $webfont_loader->extra['after'];

        if (empty($inline_scripts)) {
            return;
        }

        foreach ($inline_scripts as &$script) {
            if (strpos($script, 'mk_google_fonts') !== false) {
                $script = preg_replace('/({[\s]*families:[\s]*).*?(})/s', '$1' . mk_google_fonts() . '$2', $script);
            }
        }

        $is_modified = array_diff($webfont_loader->extra['after'], $inline_scripts);

        if (empty($is_modified)) {
            return;
        }

        add_filter('omgf_pro_web_font_loader_search_replace_regex', function ($regex, $config) {
            if (strpos($config, 'mk-webfontloader') !== false) {
                return '/WebFontConfig\.google.*?}/s';
            }

            return $regex;
        }, 10, 2);

        $wp_scripts->registered['mk-webfontloader']->extra['after'] = $inline_scripts;
    }

    /**
     * Load JS snippet inline to block Async Google Fonts.
     */
    public function block_async_google_fonts()
    {
        if (!isset($_GET['nomgf']) && OMGF_PRO_REMOVE_ASYNC_FONTS == 'on') {

            $suffix = $this->get_script_suffix();
?>
            <script id="omgf-pro-remove-async-google-fonts">
                <?php echo file_get_contents(OMGF_PRO_PLUGIN_DIR . "assets/js/remove-async-gfonts$suffix.js"); ?>
            </script>
<?php
        }
    }

    /**
     * Checks if debugging is enabled for local machines.
     * 
     * @return string .min | ''
     */
    public function get_script_suffix()
    {
        return defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    }
}
