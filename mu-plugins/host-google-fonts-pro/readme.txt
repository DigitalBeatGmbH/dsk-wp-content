=== OMGF Pro ===
Contributors: DaanvandenBergh
Tags: OMGF Pro, Google Fonts, Host Fonts Locally
Requires at least: 5.3
Tested up to: 5.9
Stable tag: 3.6.6
Requires PHP: 7.2

== Description ==

Replace all Google Fonts in your webpages with locally hosted versions. Also removes preconnect, dns-prefetch and preload headers.

== Changelog ==

= 3.6.6 | September 14th, 2022 =
* Improved: Process WebFont Loader now also detects minified WebFont Loader libraries (webfont.min.js)
* Improved: Remove Async Google Fonts also removes asynchronously added font files (fonts.gstatic.com/s/) and Material Icons (fonts.googleapis.com/icon) libraries.
* Fixed: Set required PHP version 7.2
* Fixed: Process Local Stylesheets now also supports @import String (e.g. @import "https://path.to/stylesheet.css") syntax.
* Added: omgf_pro_frontend_web_font_loader_script filter to allow modifying the input and output of the Process WebFont Loader option.
  - This is useful when a lot of shorthand (inline) JS is used (e.g. no semi-colons at the end of a line) in a theme and/or plugin.
* Fixed: Borlabs Cookie's cookie box wouldn't close when clicking "Accept" when Remove Async Google Fonts was enabled.
* Improved: Remove Async Google Fonts and Jupiter theme compatibility now only runs if OMGF is actually allowed to run.
  - This fixes issues with e.g. Divi's Frontend Builder not properly executing when OMGF Pro was active.
* Updated license manager to v1.11.2.

= 3.6.5 | August 10th, 2022 =
* Added: compatibility with Material Symbols Outlined, Material Icons and other (future) icon sets loaded through the Variable Fonts (CSS2) API.

= 3.6.4 | August 2nd, 2022 =
* Fixed: dev comments shouldn't be rendered in JS frontend.
* Added: debug points to relative URL rewrite methods.
* Fixed: base64 encoded strings in local stylesheets would pass as relative URLs, sometimes causing Fatal Errors.
  * This also resolves compatibility issues with WP Optimize.
* Fixed: Render blocking WebFont.load() scripts would pass by undetected.
* Updated license manager to v1.11.1.

= 3.6.3 =
* Updated license manager to v1.10.4.

= 3.6.2 =
* Added: Compatibility for dynamic CSS generators using `custom-css` as a handle.
* Added: Filter `omgf_pro_dynamic_css` to easily add additional handles for dynamic CSS generators in the future.
* Fixed: append() method wasn't saved before intercepting its async Google Fonts requests.

= 3.6.1 =
* Fixed: Updated static version, to force a browser cache update for admin JS files.

= 3.6.0 | July 20th, 2022 =
* Added: Remove Async Google Fonts feature, which allows you to remove async Google Fonts loaded using JS (`appendChild()`, `append()`, `insert()`) by your theme and/or plugins.
* Fixed: Relative local stylesheet URLs weren't handled properly.
* Fixed: when Force Font-Display and/or Process Local Stylesheets was enabled and many optimizable stylesheets were present in the frontend, this would cause slowdowns.
* Fixed: Stylesheets loaded using `@import` now get a unique identifier to prevent duplicates in cache.
* Fixed: External Web Font Loader (`webfont.js`) libraries are now properly replaced with a local copy.
* Added: Several UX tweaks when managing Optimized Fonts using Replace and Fallback Font Stack.
* Fixed: Stricter matching when looking for local stylesheets.
* Fixed: Root (/) and protocol (//) relative URLs should never be rewritten when rewriting and caching a local stylesheet.
* Fixed: Stylesheets that didn't require optimization (by Force Font-Display or Process Local Stylesheets) would be checked over and over again.

= 3.5.2 | June 25th, 2022 =
* Fixed: Jupiter Theme would throw WSOD (White Screen Of Death) when Web Font Loader detection was enabled.
* Fixed: Check if OMGF is active, before calling constants in DB migration scripts.
* Updated License Manager to v1.10.3.

= 3.5.1 | June 16th, 2022 =
* Added: filters to allow changing of the WebFontLoader detection regexes, because JS objects can be written in many ways.
* Fixed: prevent duplicate str_replace action in local stylesheets by using stricter preg_replace with regexes.
* Updated license manager to v1.10.2.

= 3.5.0 | June 14th, 2022 =
* Added: Bunny CDN compatibility
* Added: compatibility for Themify Builder, which mimics Google Fonts' API i.e. use different @font-face statements to include different subsets of the same font style.
* Fixed: when local stylesheets were located in unusual places, this would sometimes cause other local stylesheets to not be detected.
* Fixed: check if a local stylesheet actually exists, before attempting to process it.
* Updated license manager to v1.10.0.

= 3.4.5 | May 23rd, 2022 =
* Fixed: don't use WP_Filesystem to get and put file contents.
* Updated License Manager to v1.9.3.

= 3.4.4 =
* Fixed: Modify Source URL didn't (in stylesheets) after 3.4.2.

= 3.4.3 | April 8th, 2022 =
* Fixed: Force Font Display would corrupt stylesheets if a rule like `font-display: swap }` would be present.
* Added: Settings link to plugin's entry in Plugins screen for easier navigation.
* Fixed: re-factored clean up instructions (when Empty Cache Directory) is triggered to comply with OMGF's current flow in v5.1.1 and higher.

= 3.4.2 | March 30th, 2022 =
* Fixed: Process Local Stylesheets and Force Font Display would sometimes break src URLs of images embedded in stylesheets.
* Improved: Fonts Source URL option now also rewrites the URL used for loading the stylesheet.

= 3.4.1 | March 22nd, 2022 =
* Fixed: Process Local Stylesheets didn't work in v3.4.0.
* Fixed: Jupiter compatibility ran even when Jupiter wasn't the active theme.

= 3.4.0 | March 21st, 2022 =
* Added: Compatibility for Jupiter Theme.
* Added: Full Multisite Support. OMGF Pro automatically generates seperate stylesheets for each site in a multisite network, the cache directory can be found under Advanced Settings.
* Fixed: Cache would be marked as stale when Force Subsets and/or Include File Types options were modified.
* Fixed: WebFont Loader stylesheets couldn't be fully unloaded (i.e. removed) from page source.

= 3.3.2 | March 13th, 2022 =
* Added: Compatibility with themes using Dynamic CSS generation scrips (like Kirki based themes)
* Fixed: Memory leak in omgf_pro_processed_local_stylesheets option.
* Fixed: Cached Stylesheets containing relative URLs to font files would cause 404s if only a font-display attribute was inserted.
* Removed: since AMP no longer supports stylesheets containing custom fonts, the AMP handling feature was removed.

= 3.3.1 | March 7th, 2022 =
* Fixed: Some WebFont Loader notations would fail detection.
* Fixed: Relative URLs wouldn't be converted in stylesheets if only existing font-display attributes were replaced using the Force Font Display option.
* Fixed: Force Subsets option didn't work (OMGF 5.0.2 or higher required)

= 3.3.0 | March 4th, 2022 =
* Optimized: completely refactored the Advanced Processing logic, improving it's speed with over 1.000.000% (no, seriously, it's super fast now)
  - No more layout breaks,
  - No more time outs while downloading,
  - No more missing stylesheets on certain pages,
  - No more endless nights of crying with a pillow over your head, because your website is displaying system fonts instead of Google Fonts (sorry for that, btw.)
  - No more!
* Added: Files detected in @font-face statements will now be loaded in the proper subset, when a unicode range is defined.
* Fixed: Fallback Font Stacks and Force Font-Display option are now applied to inline <style> blocks.
* Fixed: Fonts Source URL shouldn't have a default option, to prevent later issues if e.g. the Cache Directory is changed.
* Updated: License Manager to v1.9.2.
* Several bugfixes and code optimizations.

= 3.2.2 | February 16th, 2022 =
* Added: Debug information when Scan Posts/Pages mode is used in frontend.
* Fixed: Show notice when OMGF Pro is activated without OMGF being installed/activated.
* Optimized: Minor performance improvement when fetching cache handles.
* Fixed: missing closing tag on settings page.
* UX: Added clear Pro's/Con's to Scan Posts/Pages Task Manager.

= 3.2.1 | February 5th, 2022 =
* Fixed: Since WP 5.9, Scan Posts/Pages mode would only replace Google Fonts on a limited amount of pages.
* Fixed: Array to string conversion in frontend.
* Fixed: OMGF_PRO_DEBUG_MODE can now be set from wp-config.php to enable debugging.

= 3.2.0 | February 2nd, 2022 =
* Added: Fallback Font Stacks can now complete Replace a Google Fonts font-family, by checking the new Replace box.
* Added: basic debugging throughout the plugin (can be enabled by setting OMGF_PRO_DEBUG_MODE to true).
* Improved: Fallback Font Stacks performance.
* Updated: License Manager now includes the following features:
  - Updates for OMGF Pro are now blocked until OMGF runs at the latest version.
  - License information can be refreshed (e.g. after a renewal) using the 'Just renewed?' link.
* Fixed: Commented code in CSS stylesheets would corrupt stylesheet when @import processing was enabled.
* Fixed: Force Font Display would severely slow down page loading time.
* Fixed: several PHP warnings and notices.
* Fixed: Scan Posts/Pages Mode would not insert local Google Fonts on homepage, if homepage was set to a static page.

= 3.1.5 | January 26th, 2022 =
* Tested with WP 5.9
  - Fixed: WP 5.9 welcome banner was displayed in Scan Posts/Pages Mode Task Manager.

= 3.1.4 | January 7th, 2022 =
* Fixed: Invalid argument supplied for foreach in file class-html-parser.php on line 749.
* Dev: Added several filters to allow for easier extension and modification of output.
* Fixed: Added better handling for relative URLs (e.g. '../font-file.woff2' or, simply 'font-file.woff2') when force font-display is enabled.

= 3.1.3 =
* Fixed: Urgent fix in license-manager submodule to make sure updates are properly retrieved.

= 3.1.2 =
* Improved: use internal cache key to bust browser cache for stylesheets rewritten by force font-display and fallback font stacks options.

= 3.1.1 =
* Improved: Minor performance improvement when Advanced Processing is enabled.
* Fixed: Relative URLs (i.e. URLs starting with '../') are now properly rewritten before caching stylesheets when Fallback Font Stacks or Force Font-Display option is used.

= 3.1.0 | November 17th, 2021 =
* Improved: Added [Material Icons](https://fonts.google.com/icons?selected=Material+Icons+Outlined) support.
* Improved: Added license manager as submodule -- you can remove the FFW.Press License Manager plugin.
* Added: Added Force Font-display option. This new feature will rewrite existing stylesheets (added by other themes/plugins) to include the configured font-display attribute.

= 3.0.5 | October 26th, 2021 =
* Fixed: Pro options couldn't be saved.

= 3.0.4 | October 20th, 2021 =
* Fixed: Call to a member function get_cache_handle() on null.

= 3.0.3 | October 18th, 2021 =
* Fixed: Run frontend optimization on template_redirect action (instead of wp_loaded) to make sure code is only triggered in the frontend.
* Tweaked: Added welcome message to improve enrollment.
* Fixed: A bug in v3.0.2 would cause removal of external requests to fail in Manual Optimization Mode.

= 3.0.2 | October 5th, 2021 =
* Fixed: Manual Mode works again when Advanced Processing is on.
* Fixed: Empty Cache Directory broke down due to 3.0.1's dependency checks.
* Fixed: Don't throw any more notices, when optimization is finished.
* Fixed: When Scan Posts/Pages Optimization Mode hasn't processed the page yet, serve the original page, including Google Fonts.

= 3.0.1 | October 4th, 2021 =
* Fixed: prevent unsupported operand types error, by using array_merge() instead of + operand.
* Fixed: Properly check if all dependencies are installed and activated before activating OMGF Pro and loading Admin classes.

= 3.0.0 | September 29th, 2021 =
* Added: Scan Posts/Pages Optimization Mode is completely revamped!
  1. It now runs by cron schedule, instead of upon page request. I.e. no more slow downs upon first pageload!
  2. The Optimize Fonts-tab now features a full-fledged management panel, allowing you to manually trigger cron-tasks, manage batch size, etc.
  3. AOM now updates you about its progress thru notices within the Admin area.
* Improved: reduced the amount of code running in the frontend by ~60%!
* Fixed: when Advanced Processing is disabled, OMGF Pro's other features are still properly processed by the OMGF API (e.g. Combine & Dedupe)
* Improved: several code refactors, optimizations and UX tweaks.

= 2.5.3 | August 18th, 2021 =
* Improved: calls to OMGF's download API should include a nonce.

= 2.5.2 | August 17th, 2021 =
* Fixed: "Too few arguments to function passthru_handle()" error would occur if OMGF Pro was updated to v2.5.1 before OMGF was updated to 4.5.2.
* Fixed: "Uncaught Error: Function name must be a string" error.

= 2.5.1 | August 15th, 2021 =
* Improved: Added @font-face detection in local stylesheets to Google Fonts Processing (Pro).
* Fixed: Fixed fatal error when OMGF was deactivated/removed, while OMGF Pro was active.
* Fixed: Fallback Font Stacks are now properly added to local stylesheets.
* Improved: Huge performance boost! Reduced code footprint in frontend by ~33%. Instead of queueing and processing elements for removal and replacement seperately, it's now all done at once.
* Fixed: Fixed several warnings and notices.
* Fixed: (Rewritten) local stylesheets are now properly refreshed, after changes are made to Fallback Font Stacks.

= 2.5.0 | August 2nd, 2021 =
* Added: Added Fallback Font Stack feature.
* Fixed: Fixed warning when Relative URLs are used.
* Fixed: When a manual Save & Optimize is triggered from within the Admin area, always regenerate the stylesheet.

= 2.4.0 | July 28th, 2021 =
* Added: Include File Types allows you to specify which files to include in the stylesheet. If you used the WOFF2 Only option previously, this option is now set to only use WOFF2.
* Added: CDN URL, Alternative Relative Path and Use Relative URLs are replaced by the Fonts Source URL option. Don't worry. All your settings in the previously mentioned options are properly translated/migrated to this option.
* Added: Added AMP handling feature to allow proper fallback/remove behaviour of Google Fonts on AMP pages.
* Fixed: In Manual mode, the frontend would sometimes fail to load the stylesheet early when unloads were used.

= 2.3.1 | July 5th, 2021 =
* Fixed: WP Rocket (and other CSS optimization plugins) trigger OMGF Pro multiple times. We now skip out early, if the stylesheet is already added.
* Improved: Added compatibility with Smart Slider 3.5 new implementation of Google Fonts.
  * Note: As of this version, OMGF Pro is no longer compatible with Smart Slider versions older than 3.5.

= 2.3.0 | June 7th, 2021 =
* Added: Added Exclude Post/Page IDs option
* Improved: Stylesheet is now properly placed after preloads and before other stylesheets in safe mode and default mode.
* Added: Added @import handling within theme/plugin stylesheets (@font-face handling coming soon!)
* Fixed: Webfont.js detection for default mode properly removes webfont.js objects (before a warning would be thrown)
* Allround speed/memory usage improvements.

= 2.2.1 | May 11th, 2021 =
* Improved performance in Scan Posts/Pages and Manual optimization mode.
* Improved CSS2 API handling.
* Improved Safe Mode's handling of Google Fonts.
* Several improvements and bugfixes.
* A proper warning is now displayed when attempted to activate this plugin, without OMGF being installed and active.

= 2.2.0 | April 23rd, 2021 =
* Added Safe Mode option, which is to be used if (default) Advanced Processing breaks styling on certain (or all) pages.
* Updated HTML5 validator.

= 2.1.4 | April 5th, 2021 =
* When in Scan Posts/Pages mode, only the selected preloads for the currently used stylesheet should be loaded (works with OMGF 4.3.2 and higher)

= 2.1.3 | April 4th, 2021 =
* When in Manual mode, the generated stylesheet is forced throughout all pages.

= 2.1.2 | March 17th, 2021 =
* Minor code optimization for Force Subsets option.

= 2.1.1 | March 10th, 2021 =
* Adding ?nomgf=1 to any URL will now temporarily bypass fonts optimization.

= 2.1.0 | March 6th, 2021 =
* Added support for Google Early Access Fonts. More info: https://fonts.google.com/earlyaccess

= 2.0.6 | February 1st, 2021 =
* Tested with WP 5.6

= 2.0.5 | December 22nd, 2020 =
* Add support for webfont.min.js.

= 2.0.4 | December 8th, 2020 =
* Fixed CSS2 support.
* Fixed detection and removal for @import statements.
* OMGF Pro now uses OMGF's fixed cache keys when unloads are used.

= 2.0.3 | October 7th, 2020 =
* **NOTICE: To use OMGFv4.2's Optimized Google Fonts overview it's required to Empty the Cache Directory first.**
* Compatibility fixes for OMGF 4.2.0's Optimization Mode and Do Not Load options.
* Cleaned up sidebar.

= 2.0.2 | October 1st, 2020 =
* Compatibility fixes for the way OMGF 4.1.3 handles notices.

= 2.0.1 =
* If Force Subsets wasn't set, OMGF Pro would throw a warning. This is fixed.

= 2.0.0 | September 30th, 2020 =
* OMGF Pro now detects and caches Google Fonts automatically, no more auto detect required. Even if you use different fonts on different pages, they'll be cached and served properly.
* OMGF Pro can now be temporarily disabled by disabling the Advanced Processing option under Settings > Optimize Google Fonts > Basic Settings.
* Using Subset Forcing, you can now force your theme/plugins to load all Google Fonts in a certain subset, further reducing Page Size and Load Time.
* All promotion for OMGF Pro is now removed after activating this plugin.
* Some Pro options are moved to Basic Settings and Advanced Settings, since Extensions tab is removed in OMGF 4.0.0.
* Increased compatibility with CSS Minify/Combine and Caching plugins, e.g. WP Fastest Cache, WP Rocket, WP Super Cache and Autoptimize plugins.

= 1.4.1 | September 15th, 2020 =
* Tested with WP 5.5.
* Removed Updater-classes and files, as updates are now fully managed by FFWP License Manager, significantly reducing the footprint of this plugin.
* Removed dependency of FFWP License Manager, since the two can now function fully autonomously.
* Performance improvements for class loader.

= 1.4.0 | August 16th, 2020 =
* Auto Remove Pro's behavior can now be fine tuned (within OMGF v3.8.0's Extensions tab) to speed up performance.

= 1.3.0 | July 31st, 2020 =
* OMGF Pro can now Auto Detect and Remove Google Fonts from inline stylesheets containing @font-face and @import rules.
* Minor code optimizations.
* Added dates to this changelog. :)

= 1.2.3 | July 19th, 2020 =
* Added compatibility for Smart Slider 3.
  * Smart Slider 3 users should disable Google Fonts in Smart Slider > Dashboard > Settings > Fonts > Google > Frontend [off] after running Auto Detect.
* Performance improvements.

= 1.2.2 | June 28th, 2020 =
* Fixed bug where OMGF Pro would attempt to process other documents besides valid HTML.

= 1.2.1 | June 21st, 2020 =
* Fixed bug where OMGF Pro would also process XML documents, breaking e.g. RSS feeds.

= 1.2.0 | June 10th, 2020 =
* Added support for synchronously loaded Web Font Loader.
* Added support for WebFontConfig added with base64 encoded string.
* Added a little 'eye candy' under Settings > Optimize Google Fonts, to make it more clear that OMGF Pro is enabled and functioning properly.

= 1.1.4 | June 7th, 2020 =
* OMGF Pro can now properly handle HTML5.
* Minor performance optimizations.

= 1.1.3 =
* Remove WebFontConfig script when Remove Google Fonts is enabled.

= 1.1.2 =
* Modify review and tweet link in notice after generating stylesheet to point to ffw.press.

= 1.1.1 =
* Fixed bug where Auto Detect would trigger on each page load.
* Auto Remove of DNS Prefetch, Preconnect and Preload works more accurately now.

= 1.1.0 =
* Added support for WebFont Loader.

= 1.0.0 | June 6th, 2020 =
* First Release!