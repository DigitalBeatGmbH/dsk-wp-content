<?php
/**
 * Class for providing debug data based on a users WordPress environment.
 *
 * @package Health Check
 */

// Make sure the file is not directly accessible.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

/**
 * Class Health_Check_Debug_Data
 */
class Health_Check_Debug_Data {

	/**
	 * Calls all core functions to check for updates
	 *
	 * @uses wp_version_check()
	 * @uses wp_update_plugins()
	 * @uses wp_update_themes()
	 *
	 * @return void
	 */
	static function check_for_updates() {

		wp_version_check();
		wp_update_plugins();
		wp_update_themes();

	}

	/**
	 * Static function for generating site debug data when required.
	 *
	 * @since 5.2.0
	 *
	 * @throws ImagickException
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array The debug data for the site.
	 */
	static function debug_data() {
		global $wpdb, $is_apache;

		// Save few function calls.
		$upload_dir             = wp_get_upload_dir();
		$permalink_structure    = get_option( 'permalink_structure' );
		$is_ssl                 = is_ssl();
		$users_can_register     = get_option( 'users_can_register' );
		$default_comment_status = get_option( 'default_comment_status' );
		$is_multisite           = is_multisite();
		$core_version           = get_bloginfo( 'version' );
		$core_updates           = get_core_updates();
		$core_update_needed     = '';

		foreach ( $core_updates as $core => $update ) {
			if ( 'upgrade' === $update->response ) {
				// translators: %s: Latest WordPress version number.
				$core_update_needed = ' ' . sprintf( __( '(Latest version: %s)', 'health-check' ), $update->version );
			} else {
				$core_update_needed = '';
			}
		}

		// Set up the array that holds all debug information.
		$info = array();

		$info['wp-core'] = array(
			'label'  => __( 'WordPress', 'health-check' ),
			'fields' => array(
				'version'                => array(
					'label' => __( 'Version', 'health-check' ),
					'value' => $core_version . $core_update_needed,
					'debug' => $core_version,
				),
				'site_language'          => array(
					'label' => __( 'Site Language', 'health-check' ),
					'value' => get_locale(),
				),
				'user_language'          => array(
					'label' => __( 'User Language', 'health-check' ),
					'value' => get_user_locale(),
				),
				'home_url'               => array(
					'label'   => __( 'Home URL', 'health-check' ),
					'value'   => get_bloginfo( 'url' ),
					'private' => true,
				),
				'site_url'               => array(
					'label'   => __( 'Site URL', 'health-check' ),
					'value'   => get_bloginfo( 'wpurl' ),
					'private' => true,
				),
				'permalink'              => array(
					'label' => __( 'Permalink structure', 'health-check' ),
					'value' => $permalink_structure ? $permalink_structure : __( 'No permalink structure set', 'health-check' ),
					'debug' => $permalink_structure,
				),
				'https_status'           => array(
					'label' => __( 'Is this site using HTTPS?', 'health-check' ),
					'value' => $is_ssl ? __( 'Yes', 'health-check' ) : __( 'No', 'health-check' ),
					'debug' => $is_ssl,
				),
				'user_registration'      => array(
					'label' => __( 'Can anyone register on this site?', 'health-check' ),
					'value' => $users_can_register ? __( 'Yes', 'health-check' ) : __( 'No', 'health-check' ),
					'debug' => $users_can_register,
				),
				'default_comment_status' => array(
					'label' => __( 'Default comment status', 'health-check' ),
					'value' => 'open' === $default_comment_status ? _x( 'Open', 'comment status', 'health-check' ) : _x( 'Closed', 'comment status', 'health-check' ),
					'debug' => $default_comment_status,
				),
				'multisite'              => array(
					'label' => __( 'Is this a multisite?', 'health-check' ),
					'value' => $is_multisite ? __( 'Yes', 'health-check' ) : __( 'No', 'health-check' ),
					'debug' => $is_multisite,
				),
			),
		);

		if ( ! $is_multisite ) {
			$info['wp-paths-sizes'] = array(
				'label'  => __( 'Directories and Sizes', 'health-check' ),
				'fields' => array(),
			);
		}

		$info['wp-dropins'] = array(
			'label'       => __( 'Drop-ins', 'health-check' ),
			'show_count'  => true,
			'description' => __( 'Drop-ins are single files that replace or enhance WordPress features in ways that are not possible for traditional plugins.', 'health-check' ),
			'fields'      => array(),
		);

		$info['wp-active-theme'] = array(
			'label'  => __( 'Active Theme', 'health-check' ),
			'fields' => array(),
		);

		$info['wp-parent-theme'] = array(
			'label'  => __( 'Parent Theme', 'health-check' ),
			'fields' => array(),
		);

		$info['wp-themes-inactive'] = array(
			'label'      => __( 'Inactive Themes', 'health-check' ),
			'show_count' => true,
			'fields'     => array(),
		);

		$info['wp-mu-plugins'] = array(
			'label'      => __( 'Must Use Plugins', 'health-check' ),
			'show_count' => true,
			'fields'     => array(),
		);

		$info['wp-plugins-active'] = array(
			'label'      => __( 'Active Plugins', 'health-check' ),
			'show_count' => true,
			'fields'     => array(),
		);

		$info['wp-plugins-inactive'] = array(
			'label'      => __( 'Inactive Plugins', 'health-check' ),
			'show_count' => true,
			'fields'     => array(),
		);

		$info['wp-media'] = array(
			'label'  => __( 'Media Handling', 'health-check' ),
			'fields' => array(),
		);

		$info['wp-server'] = array(
			'label'       => __( 'Server', 'health-check' ),
			'description' => __( 'The options shown below relate to your server setup. If changes are required, you may need your web host&#8217;s assistance.', 'health-check' ),
			'fields'      => array(),
		);

		$info['wp-database'] = array(
			'label'  => __( 'Database', 'health-check' ),
			'fields' => array(),
		);

		// Check if WP_DEBUG_LOG is set.
		$wp_debug_log_value = __( 'Disabled', 'health-check' );

		if ( is_string( WP_DEBUG_LOG ) ) {
			$wp_debug_log_value = WP_DEBUG_LOG;
		} elseif ( WP_DEBUG_LOG ) {
			$wp_debug_log_value = __( 'Enabled', 'health-check' );
		}

		// Check CONCATENATE_SCRIPTS.
		if ( defined( 'CONCATENATE_SCRIPTS' ) ) {
			$concatenate_scripts       = CONCATENATE_SCRIPTS ? __( 'Enabled', 'health-check' ) : __( 'Disabled', 'health-check' );
			$concatenate_scripts_debug = CONCATENATE_SCRIPTS ? 'true' : 'false';
		} else {
			$concatenate_scripts       = __( 'Undefined', 'health-check' );
			$concatenate_scripts_debug = 'undefined';
		}

		// Check COMPRESS_SCRIPTS.
		if ( defined( 'COMPRESS_SCRIPTS' ) ) {
			$compress_scripts       = COMPRESS_SCRIPTS ? __( 'Enabled', 'health-check' ) : __( 'Disabled', 'health-check' );
			$compress_scripts_debug = COMPRESS_SCRIPTS ? 'true' : 'false';
		} else {
			$compress_scripts       = __( 'Undefined', 'health-check' );
			$compress_scripts_debug = 'undefined';
		}

		// Check COMPRESS_CSS.
		if ( defined( 'COMPRESS_CSS' ) ) {
			$compress_css       = COMPRESS_CSS ? __( 'Enabled', 'health-check' ) : __( 'Disabled', 'health-check' );
			$compress_css_debug = COMPRESS_CSS ? 'true' : 'false';
		} else {
			$compress_css       = __( 'Undefined', 'health-check' );
			$compress_css_debug = 'undefined';
		}

		// Check WP_LOCAL_DEV.
		if ( defined( 'WP_LOCAL_DEV' ) ) {
			$wp_local_dev       = WP_LOCAL_DEV ? __( 'Enabled', 'health-check' ) : __( 'Disabled', 'health-check' );
			$wp_local_dev_debug = WP_LOCAL_DEV ? 'true' : 'false';
		} else {
			$wp_local_dev       = __( 'Undefined', 'health-check' );
			$wp_local_dev_debug = 'undefined';
		}

		$info['wp-constants'] = array(
			'label'       => __( 'WordPress Constants', 'health-check' ),
			'description' => __( 'These settings alter where and how parts of WordPress are loaded.', 'health-check' ),
			'fields'      => array(
				'ABSPATH'             => array(
					'label'   => 'ABSPATH',
					'value'   => ABSPATH,
					'private' => true,
				),
				'WP_HOME'             => array(
					'label' => 'WP_HOME',
					'value' => ( defined( 'WP_HOME' ) ? WP_HOME : __( 'Undefined', 'health-check' ) ),
					'debug' => ( defined( 'WP_HOME' ) ? WP_HOME : 'undefined' ),
				),
				'WP_SITEURL'          => array(
					'label' => 'WP_SITEURL',
					'value' => ( defined( 'WP_SITEURL' ) ? WP_SITEURL : __( 'Undefined', 'health-check' ) ),
					'debug' => ( defined( 'WP_SITEURL' ) ? WP_SITEURL : 'undefined' ),
				),
				'WP_CONTENT_DIR'      => array(
					'label' => 'WP_CONTENT_DIR',
					'value' => WP_CONTENT_DIR,
				),
				'WP_PLUGIN_DIR'       => array(
					'label' => 'WP_PLUGIN_DIR',
					'value' => WP_PLUGIN_DIR,
				),
				'WP_MAX_MEMORY_LIMIT' => array(
					'label' => 'WP_MAX_MEMORY_LIMIT',
					'value' => WP_MAX_MEMORY_LIMIT,
				),
				'WP_DEBUG'            => array(
					'label' => 'WP_DEBUG',
					'value' => WP_DEBUG ? __( 'Enabled', 'health-check' ) : __( 'Disabled', 'health-check' ),
					'debug' => WP_DEBUG,
				),
				'WP_DEBUG_DISPLAY'    => array(
					'label' => 'WP_DEBUG_DISPLAY',
					'value' => WP_DEBUG_DISPLAY ? __( 'Enabled', 'health-check' ) : __( 'Disabled', 'health-check' ),
					'debug' => WP_DEBUG_DISPLAY,
				),
				'WP_DEBUG_LOG'        => array(
					'label' => 'WP_DEBUG_LOG',
					'value' => $wp_debug_log_value,
					'debug' => WP_DEBUG_LOG,
				),
				'SCRIPT_DEBUG'        => array(
					'label' => 'SCRIPT_DEBUG',
					'value' => SCRIPT_DEBUG ? __( 'Enabled', 'health-check' ) : __( 'Disabled', 'health-check' ),
					'debug' => SCRIPT_DEBUG,
				),
				'WP_CACHE'            => array(
					'label' => 'WP_CACHE',
					'value' => WP_CACHE ? __( 'Enabled', 'health-check' ) : __( 'Disabled', 'health-check' ),
					'debug' => WP_CACHE,
				),
				'CONCATENATE_SCRIPTS' => array(
					'label' => 'CONCATENATE_SCRIPTS',
					'value' => $concatenate_scripts,
					'debug' => $concatenate_scripts_debug,
				),
				'COMPRESS_SCRIPTS'    => array(
					'label' => 'COMPRESS_SCRIPTS',
					'value' => $compress_scripts,
					'debug' => $compress_scripts_debug,
				),
				'COMPRESS_CSS'        => array(
					'label' => 'COMPRESS_CSS',
					'value' => $compress_css,
					'debug' => $compress_css_debug,
				),
				'WP_LOCAL_DEV'        => array(
					'label' => 'WP_LOCAL_DEV',
					'value' => $wp_local_dev,
					'debug' => $wp_local_dev_debug,
				),
			),
		);

		$is_writable_abspath            = wp_is_writable( ABSPATH );
		$is_writable_wp_content_dir     = wp_is_writable( WP_CONTENT_DIR );
		$is_writable_upload_dir         = wp_is_writable( $upload_dir['basedir'] );
		$is_writable_wp_plugin_dir      = wp_is_writable( WP_PLUGIN_DIR );
		$is_writable_template_directory = wp_is_writable( get_template_directory() . '/..' );

		$info['wp-filesystem'] = array(
			'label'       => __( 'Filesystem Permissions', 'health-check' ),
			'description' => __( 'Shows whether WordPress is able to write to the directories it needs access to.', 'health-check' ),
			'fields'      => array(
				'wordpress'  => array(
					'label' => __( 'The main WordPress directory', 'health-check' ),
					'value' => ( $is_writable_abspath ? __( 'Writable', 'health-check' ) : __( 'Not writable', 'health-check' ) ),
					'debug' => ( $is_writable_abspath ? 'writable' : 'not writable' ),
				),
				'wp-content' => array(
					'label' => __( 'The wp-content directory', 'health-check' ),
					'value' => ( $is_writable_wp_content_dir ? __( 'Writable', 'health-check' ) : __( 'Not writable', 'health-check' ) ),
					'debug' => ( $is_writable_wp_content_dir ? 'writable' : 'not writable' ),
				),
				'uploads'    => array(
					'label' => __( 'The uploads directory', 'health-check' ),
					'value' => ( $is_writable_upload_dir ? __( 'Writable', 'health-check' ) : __( 'Not writable', 'health-check' ) ),
					'debug' => ( $is_writable_upload_dir ? 'writable' : 'not writable' ),
				),
				'plugins'    => array(
					'label' => __( 'The plugins directory', 'health-check' ),
					'value' => ( $is_writable_wp_plugin_dir ? __( 'Writable', 'health-check' ) : __( 'Not writable', 'health-check' ) ),
					'debug' => ( $is_writable_wp_plugin_dir ? 'writable' : 'not writable' ),
				),
				'themes'     => array(
					'label' => __( 'The themes directory', 'health-check' ),
					'value' => ( $is_writable_template_directory ? __( 'Writable', 'health-check' ) : __( 'Not writable', 'health-check' ) ),
					'debug' => ( $is_writable_template_directory ? 'writable' : 'not writable' ),
				),
			),
		);

		// Conditionally add debug information for multisite setups.
		if ( is_multisite() ) {
			$network_query = new WP_Network_Query();
			$network_ids   = $network_query->query(
				array(
					'fields'        => 'ids',
					'number'        => 100,
					'no_found_rows' => false,
				)
			);

			$site_count = 0;
			foreach ( $network_ids as $network_id ) {
				$site_count += get_blog_count( $network_id );
			}

			$info['wp-core']['fields']['user_count'] = array(
				'label' => __( 'User count', 'health-check' ),
				'value' => get_user_count(),
			);

			$info['wp-core']['fields']['site_count'] = array(
				'label' => __( 'Site count', 'health-check' ),
				'value' => $site_count,
			);

			$info['wp-core']['fields']['network_count'] = array(
				'label' => __( 'Network count', 'health-check' ),
				'value' => $network_query->found_networks,
			);
		} else {
			$user_count = count_users();

			$info['wp-core']['fields']['user_count'] = array(
				'label' => __( 'User count', 'health-check' ),
				'value' => $user_count['total_users'],
			);
		}

		// WordPress features requiring processing.
		$wp_dotorg = wp_remote_get( 'https://api.wordpress.org', array( 'timeout' => 10 ) );

		if ( ! is_wp_error( $wp_dotorg ) ) {
			$info['wp-core']['fields']['dotorg_communication'] = array(
				'label' => __( 'Communication with WordPress.org', 'health-check' ),
				'value' => __( 'WordPress.org is reachable', 'health-check' ),
				'debug' => 'true',
			);
		} else {
			$info['wp-core']['fields']['dotorg_communication'] = array(
				'label' => __( 'Communication with WordPress.org', 'health-check' ),
				'value' => sprintf(
					// translators: 1: The IP address WordPress.org resolves to. 2: The error returned by the lookup.
					__( 'Unable to reach WordPress.org at %1$s: %2$s', 'health-check' ),
					gethostbyname( 'api.wordpress.org' ),
					$wp_dotorg->get_error_message()
				),
				'debug' => $wp_dotorg->get_error_message(),
			);
		}

		// Remove accordion for Directories and Sizes if in Multisite.
		if ( ! $is_multisite ) {
			$loading = __( 'Loading&hellip;', 'health-check' );

			$info['wp-paths-sizes']['fields'] = array(
				'wordpress_path' => array(
					'label' => __( 'WordPress directory location', 'health-check' ),
					'value' => untrailingslashit( ABSPATH ),
				),
				'wordpress_size' => array(
					'label' => __( 'WordPress directory size', 'health-check' ),
					'value' => $loading,
					'debug' => 'loading...',
				),
				'uploads_path'   => array(
					'label' => __( 'Uploads directory location', 'health-check' ),
					'value' => $upload_dir['basedir'],
				),
				'uploads_size'   => array(
					'label' => __( 'Uploads directory size', 'health-check' ),
					'value' => $loading,
					'debug' => 'loading...',
				),
				'themes_path'    => array(
					'label' => __( 'Themes directory location', 'health-check' ),
					'value' => get_theme_root(),
				),
				'themes_size'    => array(
					'label' => __( 'Themes directory size', 'health-check' ),
					'value' => $loading,
					'debug' => 'loading...',
				),
				'plugins_path'   => array(
					'label' => __( 'Plugins directory location', 'health-check' ),
					'value' => WP_PLUGIN_DIR,
				),
				'plugins_size'   => array(
					'label' => __( 'Plugins directory size', 'health-check' ),
					'value' => $loading,
					'debug' => 'loading...',
				),
				'database_size'  => array(
					'label' => __( 'Database size', 'health-check' ),
					'value' => $loading,
					'debug' => 'loading...',
				),
				'total_size'     => array(
					'label' => __( 'Total installation size', 'health-check' ),
					'value' => $loading,
					'debug' => 'loading...',
				),
			);
		}

		// Get a list of all drop-in replacements.
		$dropins = get_dropins();

		// Get dropins descriptions.
		$dropin_descriptions = _get_dropins();

		// Spare few function calls.
		$not_available = __( 'Not available', 'health-check' );

		foreach ( $dropins as $dropin_key => $dropin ) {
			$info['wp-dropins']['fields'][ sanitize_text_field( $dropin_key ) ] = array(
				'label' => $dropin_key,
				'value' => $dropin_descriptions[ $dropin_key ][0],
				'debug' => 'true',
			);
		}

		// Populate the media fields.
		$info['wp-media']['fields']['image_editor'] = array(
			'label' => __( 'Active editor', 'health-check' ),
			'value' => _wp_image_editor_choose(),
		);

		// Get ImageMagic information, if available.
		if ( class_exists( 'Imagick' ) ) {
			// Save the Imagick instance for later use.
			$imagick         = new Imagick();
			$imagick_version = $imagick->getVersion();
		} else {
			$imagick_version = __( 'Not available', 'health-check' );
		}

		$info['wp-media']['fields']['imagick_module_version'] = array(
			'label' => __( 'ImageMagick version number', 'health-check' ),
			'value' => ( is_array( $imagick_version ) ? $imagick_version['versionNumber'] : $imagick_version ),
		);

		$info['wp-media']['fields']['imagemagick_version'] = array(
			'label' => __( 'ImageMagick version string', 'health-check' ),
			'value' => ( is_array( $imagick_version ) ? $imagick_version['versionString'] : $imagick_version ),
		);

		// If Imagick is used as our editor, provide some more information about its limitations.
		if ( 'WP_Image_Editor_Imagick' === _wp_image_editor_choose() && isset( $imagick ) && $imagick instanceof Imagick ) {
			$limits = array(
				'area'   => ( defined( 'imagick::RESOURCETYPE_AREA' ) ? size_format( $imagick->getResourceLimit( imagick::RESOURCETYPE_AREA ) ) : $not_available ),
				'disk'   => ( defined( 'imagick::RESOURCETYPE_DISK' ) ? $imagick->getResourceLimit( imagick::RESOURCETYPE_DISK ) : $not_available ),
				'file'   => ( defined( 'imagick::RESOURCETYPE_FILE' ) ? $imagick->getResourceLimit( imagick::RESOURCETYPE_FILE ) : $not_available ),
				'map'    => ( defined( 'imagick::RESOURCETYPE_MAP' ) ? size_format( $imagick->getResourceLimit( imagick::RESOURCETYPE_MAP ) ) : $not_available ),
				'memory' => ( defined( 'imagick::RESOURCETYPE_MEMORY' ) ? size_format( $imagick->getResourceLimit( imagick::RESOURCETYPE_MEMORY ) ) : $not_available ),
				'thread' => ( defined( 'imagick::RESOURCETYPE_THREAD' ) ? $imagick->getResourceLimit( imagick::RESOURCETYPE_THREAD ) : $not_available ),
			);

			$limits_debug = array(
				'imagick::RESOURCETYPE_AREA'   => ( defined( 'imagick::RESOURCETYPE_AREA' ) ? size_format( $imagick->getResourceLimit( imagick::RESOURCETYPE_AREA ) ) : 'not available' ),
				'imagick::RESOURCETYPE_DISK'   => ( defined( 'imagick::RESOURCETYPE_DISK' ) ? $imagick->getResourceLimit( imagick::RESOURCETYPE_DISK ) : 'not available' ),
				'imagick::RESOURCETYPE_FILE'   => ( defined( 'imagick::RESOURCETYPE_FILE' ) ? $imagick->getResourceLimit( imagick::RESOURCETYPE_FILE ) : 'not available' ),
				'imagick::RESOURCETYPE_MAP'    => ( defined( 'imagick::RESOURCETYPE_MAP' ) ? size_format( $imagick->getResourceLimit( imagick::RESOURCETYPE_MAP ) ) : 'not available' ),
				'imagick::RESOURCETYPE_MEMORY' => ( defined( 'imagick::RESOURCETYPE_MEMORY' ) ? size_format( $imagick->getResourceLimit( imagick::RESOURCETYPE_MEMORY ) ) : 'not available' ),
				'imagick::RESOURCETYPE_THREAD' => ( defined( 'imagick::RESOURCETYPE_THREAD' ) ? $imagick->getResourceLimit( imagick::RESOURCETYPE_THREAD ) : 'not available' ),
			);

			$info['wp-media']['fields']['imagick_limits'] = array(
				'label' => __( 'Imagick Resource Limits', 'health-check' ),
				'value' => $limits,
				'debug' => $limits_debug,
			);
		}

		// Get GD information, if available.
		if ( function_exists( 'gd_info' ) ) {
			$gd = gd_info();
		} else {
			$gd = false;
		}

		$info['wp-media']['fields']['gd_version'] = array(
			'label' => __( 'GD version', 'health-check' ),
			'value' => ( is_array( $gd ) ? $gd['GD Version'] : $not_available ),
			'debug' => ( is_array( $gd ) ? $gd['GD Version'] : 'not available' ),
		);

		// Get Ghostscript information, if available.
		if ( function_exists( 'exec' ) ) {
			$gs = exec( 'gs --version' );

			if ( empty( $gs ) ) {
				$gs       = $not_available;
				$gs_debug = 'not available';
			} else {
				$gs_debug = $gs;
			}
		} else {
			$gs       = __( 'Unable to determine if Ghostscript is installed', 'health-check' );
			$gs_debug = 'unknown';
		}

		$info['wp-media']['fields']['ghostscript_version'] = array(
			'label' => __( 'Ghostscript version', 'health-check' ),
			'value' => $gs,
			'debug' => $gs_debug,
		);

		// Populate the server debug fields.
		if ( function_exists( 'php_uname' ) ) {
			$server_architecture = sprintf( '%s %s %s', php_uname( 's' ), php_uname( 'r' ), php_uname( 'm' ) );
		} else {
			$server_architecture = 'unknown';
		}

		if ( function_exists( 'phpversion' ) ) {
			$php_version_debug = phpversion();
			// Whether PHP supports 64bit
			$php64bit = ( PHP_INT_SIZE * 8 === 64 );

			$php_version = sprintf(
				'%s %s',
				$php_version_debug,
				( $php64bit ? __( '(Supports 64bit values)', 'health-check' ) : __( '(Does not support 64bit values)', 'health-check' ) )
			);

			if ( $php64bit ) {
				$php_version_debug .= ' 64bit';
			}
		} else {
			$php_version       = __( 'Unable to determine PHP version', 'health-check' );
			$php_version_debug = 'unknown';
		}

		if ( function_exists( 'php_sapi_name' ) ) {
			$php_sapi = php_sapi_name();
		} else {
			$php_sapi = 'unknown';
		}

		if ( function_exists( 'get_current_user' ) && function_exists( 'getmyuid' ) ) {
			$php_getuid = sprintf(
				'%s (%s)',
				get_current_user(),
				getmyuid()
			);
		} else {
			$php_getuid = 'unknown';
		}

		$info['wp-server']['fields']['server_architecture'] = array(
			'label' => __( 'Server architecture', 'health-check' ),
			'value' => ( 'unknown' !== $server_architecture ? $server_architecture : __( 'Unable to determine server architecture', 'health-check' ) ),
			'debug' => $server_architecture,
		);
		$info['wp-server']['fields']['php-uid']             = array(
			'label'   => __( 'Website server user', 'health-check' ),
			'value'   => ( 'unknown' !== $php_getuid ? $php_getuid : __( 'Unable to determine the websites server user', 'health-check' ) ),
			'debug'   => $php_getuid,
			'private' => true,
		);
		$info['wp-server']['fields']['httpd_software']      = array(
			'label' => __( 'Web server', 'health-check' ),
			'value' => ( isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : __( 'Unable to determine what web server software is used', 'health-check' ) ),
			'debug' => ( isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown' ),
		);
		$info['wp-server']['fields']['php_version']         = array(
			'label' => __( 'PHP version', 'health-check' ),
			'value' => $php_version,
			'debug' => $php_version_debug,
		);
		$info['wp-server']['fields']['php_sapi']            = array(
			'label' => __( 'PHP SAPI', 'health-check' ),
			'value' => ( 'unknown' !== $php_sapi ? $php_sapi : __( 'Unable to determine PHP SAPI', 'health-check' ) ),
			'debug' => $php_sapi,
		);

		// Some servers disable `ini_set()` and `ini_get()`, we check this before trying to get configuration values.
		if ( ! function_exists( 'ini_get' ) ) {
			$info['wp-server']['fields']['ini_get'] = array(
				'label' => __( 'Server settings', 'health-check' ),
				'value' => __( 'Unable to determine some settings, as the ini_get() function has been disabled.', 'health-check' ),
				'debug' => 'ini_get() is disabled',
			);
		} else {
			$info['wp-server']['fields']['max_input_variables'] = array(
				'label' => __( 'PHP max input variables', 'health-check' ),
				'value' => ini_get( 'max_input_vars' ),
			);
			$info['wp-server']['fields']['time_limit']          = array(
				'label' => __( 'PHP time limit', 'health-check' ),
				'value' => ini_get( 'max_execution_time' ),
			);
			$info['wp-server']['fields']['memory_limit']        = array(
				'label' => __( 'PHP memory limit', 'health-check' ),
				'value' => ini_get( 'memory_limit' ),
			);
			$info['wp-server']['fields']['max_input_time']      = array(
				'label' => __( 'Max input time', 'health-check' ),
				'value' => ini_get( 'max_input_time' ),
			);
			$info['wp-server']['fields']['upload_max_size']     = array(
				'label' => __( 'Upload max filesize', 'health-check' ),
				'value' => ini_get( 'upload_max_filesize' ),
			);
			$info['wp-server']['fields']['php_post_max_size']   = array(
				'label' => __( 'PHP post max size', 'health-check' ),
				'value' => ini_get( 'post_max_size' ),
			);
		}

		if ( function_exists( 'curl_version' ) ) {
			$curl = curl_version();

			$info['wp-server']['fields']['curl_version'] = array(
				'label' => __( 'cURL version', 'health-check' ),
				'value' => sprintf( '%s %s', $curl['version'], $curl['ssl_version'] ),
			);
		} else {
			$info['wp-server']['fields']['curl_version'] = array(
				'label' => __( 'cURL version', 'health-check' ),
				'value' => $not_available,
				'debug' => 'not available',
			);
		}

		// SUHOSIN
		$suhosin_loaded = ( extension_loaded( 'suhosin' ) || ( defined( 'SUHOSIN_PATCH' ) && constant( 'SUHOSIN_PATCH' ) ) );

		$info['wp-server']['fields']['suhosin'] = array(
			'label' => __( 'Is SUHOSIN installed?', 'health-check' ),
			'value' => ( $suhosin_loaded ? __( 'Yes', 'health-check' ) : __( 'No', 'health-check' ) ),
			'debug' => $suhosin_loaded,
		);

		// Imagick
		$imagick_loaded = extension_loaded( 'imagick' );

		$info['wp-server']['fields']['imagick_availability'] = array(
			'label' => __( 'Is the Imagick library available?', 'health-check' ),
			'value' => ( $imagick_loaded ? __( 'Yes', 'health-check' ) : __( 'No', 'health-check' ) ),
			'debug' => $imagick_loaded,
		);

		$cookies = wp_unslash( $_COOKIE );
		$timeout = 10;
		$headers = array(
			'Cache-Control' => 'no-cache',
			'X-WP-Nonce'    => wp_create_nonce( 'wp_rest' ),
		);
		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) {
			$headers['Authorization'] = 'Basic ' . base64_encode( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) . ':' . wp_unslash( $_SERVER['PHP_AUTH_PW'] ) );
		}

		$server_request = wp_remote_get( site_url(), compact( 'cookies', 'headers', 'timeout' ) );
		if ( is_wp_error( $server_request ) ) {
			$info['wp-server']['fields']['server-headers'] = array(
				'label' => __( 'Server headers', 'health-check' ),
				'value' => __( 'Could not retrieve server headers', 'health-check' ),
				'debug' => 'unknown',
			);
		} else {
			$server_headers = wp_remote_retrieve_headers( $server_request );

			$info['wp-server']['fields']['server-headers'] = array(
				'label' => __( 'Server headers', 'health-check' ),
				'value' => ( $server_headers instanceof \Requests_Utility_CaseInsensitiveDictionary ? $server_headers->getAll() : $server_headers ),
				'debug' => ( $server_headers instanceof \Requests_Utility_CaseInsensitiveDictionary ? $server_headers->getAll() : $server_headers ),
			);
		}

		// Check if a .htaccess file exists.
		if ( $is_apache && is_file( ABSPATH . '.htaccess' ) ) {
			// If the file exists, grab the content of it.
			$htaccess_content = file_get_contents( ABSPATH . '.htaccess' );

			// Filter away the core WordPress rules.
			$filtered_htaccess_content = trim( preg_replace( '/\# BEGIN WordPress[\s\S]+?# END WordPress/si', '', $htaccess_content ) );
			$filtered_htaccess_content = ! empty( $filtered_htaccess_content );

			$info['wp-server']['fields']['htaccess_extra_rules'] = array(
				'label' => __( '.htaccess rules', 'health-check' ),
				'value' => ( $filtered_htaccess_content ? __( 'Custom rules have been added to your .htaccess file.', 'health-check' ) : __( 'Your .htaccess file contains only core WordPress features.', 'health-check' ) ),
				'debug' => $filtered_htaccess_content,
			);
		}

		// Populate the database debug fields.
		if ( is_resource( $wpdb->dbh ) ) {
			// Old mysql extension.
			$extension = 'mysql';
		} elseif ( is_object( $wpdb->dbh ) ) {
			// mysqli or PDO.
			$extension = get_class( $wpdb->dbh );
		} else {
			// Unknown sql extension.
			$extension = null;
		}

		$server = $wpdb->get_var( 'SELECT VERSION()' );

		if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
			$client_version = $wpdb->dbh->client_info;
		} else {
			// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysql_get_client_info
			if ( preg_match( '|[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2}|', mysql_get_client_info(), $matches ) ) {
				$client_version = $matches[0];
			} else {
				$client_version = null;
			}
		}

		$info['wp-database']['fields']['extension'] = array(
			'label' => __( 'Extension', 'health-check' ),
			'value' => $extension,
		);

		$info['wp-database']['fields']['server_version'] = array(
			'label' => __( 'Server version', 'health-check' ),
			'value' => $server,
		);

		$info['wp-database']['fields']['client_version'] = array(
			'label' => __( 'Client version', 'health-check' ),
			'value' => $client_version,
		);

		$info['wp-database']['fields']['database_user'] = array(
			'label'   => __( 'Database user', 'health-check' ),
			'value'   => $wpdb->dbuser,
			'private' => true,
		);

		$info['wp-database']['fields']['database_host'] = array(
			'label'   => __( 'Database host', 'health-check' ),
			'value'   => $wpdb->dbhost,
			'private' => true,
		);

		$info['wp-database']['fields']['database_name'] = array(
			'label'   => __( 'Database name', 'health-check' ),
			'value'   => $wpdb->dbname,
			'private' => true,
		);

		$info['wp-database']['fields']['database_prefix'] = array(
			'label'   => __( 'Database prefix', 'health-check' ),
			'value'   => $wpdb->prefix,
			'private' => true,
		);

		// List must use plugins if there are any.
		$mu_plugins = get_mu_plugins();

		foreach ( $mu_plugins as $plugin_path => $plugin ) {
			$plugin_version = $plugin['Version'];
			$plugin_author  = $plugin['Author'];

			$plugin_version_string       = __( 'No version or author information is available.', 'health-check' );
			$plugin_version_string_debug = 'author: (undefined), version: (undefined)';

			if ( ! empty( $plugin_version ) && ! empty( $plugin_author ) ) {
				// translators: 1: Plugin version number. 2: Plugin author name.
				$plugin_version_string       = sprintf( __( 'Version %1$s by %2$s', 'health-check' ), $plugin_version, $plugin_author );
				$plugin_version_string_debug = sprintf( 'version: %s, author: %s', $plugin_version, $plugin_author );
			} else {
				if ( ! empty( $plugin_author ) ) {
					// translators: %s: Plugin author name.
					$plugin_version_string       = sprintf( __( 'By %s', 'health-check' ), $plugin_author );
					$plugin_version_string_debug = sprintf( 'author: %s, version: (undefined)', $plugin_author );
				}

				if ( ! empty( $plugin_version ) ) {
					// translators: %s: Plugin version number.
					$plugin_version_string       = sprintf( __( 'Version %s', 'health-check' ), $plugin_version );
					$plugin_version_string_debug = sprintf( 'author: (undefined), version: %s', $plugin_version );
				}
			}

			$info['wp-mu-plugins']['fields'][ sanitize_text_field( $plugin['Name'] ) ] = array(
				'label' => $plugin['Name'],
				'value' => $plugin_version_string,
				'debug' => $plugin_version_string_debug,
			);
		}

		// List all available plugins.
		$plugins        = get_plugins();
		$plugin_updates = get_plugin_updates();

		foreach ( $plugins as $plugin_path => $plugin ) {
			$plugin_part = ( is_plugin_active( $plugin_path ) ) ? 'wp-plugins-active' : 'wp-plugins-inactive';

			$plugin_version = $plugin['Version'];
			$plugin_author  = $plugin['Author'];

			$plugin_version_string       = __( 'No version or author information is available.', 'health-check' );
			$plugin_version_string_debug = 'author: (undefined), version: (undefined)';

			if ( ! empty( $plugin_version ) && ! empty( $plugin_author ) ) {
				// translators: 1: Plugin version number. 2: Plugin author name.
				$plugin_version_string       = sprintf( __( 'Version %1$s by %2$s', 'health-check' ), $plugin_version, $plugin_author );
				$plugin_version_string_debug = sprintf( 'version: %s, author: %s', $plugin_version, $plugin_author );
			} else {
				if ( ! empty( $plugin_author ) ) {
					// translators: %s: Plugin author name.
					$plugin_version_string       = sprintf( __( 'By %s', 'health-check' ), $plugin_author );
					$plugin_version_string_debug = sprintf( 'author: %s, version: (undefined)', $plugin_author );
				}

				if ( ! empty( $plugin_version ) ) {
					// translators: %s: Plugin version number.
					$plugin_version_string       = sprintf( __( 'Version %s', 'health-check' ), $plugin_version );
					$plugin_version_string_debug = sprintf( 'author: (undefined), version: %s', $plugin_version );
				}
			}

			if ( array_key_exists( $plugin_path, $plugin_updates ) ) {
				// translators: %s: Latest plugin version number.
				$plugin_version_string       .= ' ' . sprintf( __( '(Latest version: %s)', 'health-check' ), $plugin_updates[ $plugin_path ]->update->new_version );
				$plugin_version_string_debug .= sprintf( ' (latest version: %s)', $plugin_updates[ $plugin_path ]->update->new_version );
			}

			$info[ $plugin_part ]['fields'][ sanitize_text_field( $plugin['Name'] ) ] = array(
				'label' => $plugin['Name'],
				'value' => $plugin_version_string,
				'debug' => $plugin_version_string_debug,
			);
		}

		// Populate the section for the currently active theme.
		global $_wp_theme_features;
		$theme_features = array();

		if ( ! empty( $_wp_theme_features ) ) {
			foreach ( $_wp_theme_features as $feature => $options ) {
				$theme_features[] = $feature;
			}
		}

		$active_theme  = wp_get_theme();
		$theme_updates = get_theme_updates();

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$active_theme_version       = $active_theme->Version;
		$active_theme_version_debug = $active_theme_version;

		if ( array_key_exists( $active_theme->stylesheet, $theme_updates ) ) {
			$theme_update_new_version = $theme_updates[ $active_theme->stylesheet ]->update['new_version'];

			// translators: %s: Latest theme version number.
			$active_theme_version       .= ' ' . sprintf( __( '(Latest version: %s)', 'health-check' ), $theme_update_new_version );
			$active_theme_version_debug .= sprintf( ' (latest version: %s)', $theme_update_new_version );
		}

		$active_theme_author_uri = $active_theme->offsetGet( 'Author URI' );

		$info['wp-active-theme']['fields'] = array(
			'name'           => array(
				'label' => __( 'Name', 'health-check' ),
				'value' => sprintf(
					// translators: 1: Parent theme name. 2: Parent theme slug.
					__( '%1$s (%2$s)', 'health-check' ),
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$active_theme->Name,
					$active_theme->stylesheet
				),
			),
			'version'        => array(
				'label' => __( 'Version', 'health-check' ),
				'value' => $active_theme_version,
				'debug' => $active_theme_version_debug,
			),
			'author'         => array(
				'label' => __( 'Author', 'health-check' ),
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'value' => wp_kses( $active_theme->Author, array() ),
			),
			'author_website' => array(
				'label' => __( 'Author website', 'health-check' ),
				'value' => ( $active_theme_author_uri ? $active_theme_author_uri : __( 'Undefined', 'health-check' ) ),
				'debug' => ( $active_theme_author_uri ? $active_theme_author_uri : '(undefined)' ),
			),
			'parent_theme'   => array(
				'label' => __( 'Parent theme', 'health-check' ),
				'value' => ( $active_theme->parent_theme ? $active_theme->parent_theme . ' (' . $active_theme->template . ')' : __( 'None', 'health-check' ) ),
				'debug' => ( $active_theme->parent_theme ? $active_theme->parent_theme . ' (' . $active_theme->template . ')' : 'none' ),
			),
			'theme_features' => array(
				'label' => __( 'Theme features', 'health-check' ),
				'value' => implode( ', ', $theme_features ),
			),
			'theme_path'     => array(
				'label' => __( 'Theme directory location', 'health-check' ),
				'value' => get_stylesheet_directory(),
			),
		);

		$parent_theme = $active_theme->parent();

		if ( $parent_theme ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$parent_theme_version       = $parent_theme->Version;
			$parent_theme_version_debug = $parent_theme_version;

			if ( array_key_exists( $parent_theme->stylesheet, $theme_updates ) ) {
				$parent_theme_update_new_version = $theme_updates[ $parent_theme->stylesheet ]->update['new_version'];

				// translators: %s: Latest theme version number.
				$parent_theme_version       .= ' ' . sprintf( __( '(Latest version: %s)', 'health-check' ), $parent_theme_update_new_version );
				$parent_theme_version_debug .= sprintf( ' (latest version: %s)', $parent_theme_update_new_version );
			}

			$parent_theme_author_uri = $parent_theme->offsetGet( 'Author URI' );

			$info['wp-parent-theme']['fields'] = array(
				'name'           => array(
					'label' => __( 'Name', 'health-check' ),
					'value' => sprintf(
						// translators: 1: Parent theme name. 2: Parent theme slug.
						__( '%1$s (%2$s)', 'health-check' ),
						// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$parent_theme->Name,
						$parent_theme->stylesheet
					),
				),
				'version'        => array(
					'label' => __( 'Version', 'health-check' ),
					'value' => $parent_theme_version,
					'debug' => $parent_theme_version_debug,
				),
				'author'         => array(
					'label' => __( 'Author', 'health-check' ),
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					'value' => wp_kses( $parent_theme->Author, array() ),
				),
				'author_website' => array(
					'label' => __( 'Author website', 'health-check' ),
					'value' => ( $parent_theme_author_uri ? $parent_theme_author_uri : __( 'Undefined', 'health-check' ) ),
					'debug' => ( $parent_theme_author_uri ? $parent_theme_author_uri : '(undefined)' ),
				),
				'theme_path'     => array(
					'label' => __( 'Theme directory location', 'health-check' ),
					'value' => get_template_directory(),
				),
			);
		}

		// Populate a list of all themes available in the install.
		$all_themes = wp_get_themes();

		foreach ( $all_themes as $theme_slug => $theme ) {
			// Ignore the currently active theme from the list of all themes.
			if ( $active_theme->stylesheet === $theme_slug ) {
				continue;
			}

			// Ignore the currently active parent theme from the list of all themes.
			if ( ! empty( $parent_theme ) && $parent_theme->stylesheet === $theme_slug ) {
				continue;
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$theme_version = $theme->Version;
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$theme_author = $theme->Author;

			// Sanitize
			$theme_author = wp_kses( $theme_author, array() );

			$theme_version_string       = __( 'No version or author information is available.', 'health-check' );
			$theme_version_string_debug = 'undefined';

			if ( ! empty( $theme_version ) && ! empty( $theme_author ) ) {
				// translators: 1: Theme version number. 2: Theme author name.
				$theme_version_string       = sprintf( __( 'Version %1$s by %2$s', 'health-check' ), $theme_version, $theme_author );
				$theme_version_string_debug = sprintf( 'version: %s, author: %s', $theme_version, $theme_author );
			} else {
				if ( ! empty( $theme_author ) ) {
					// translators: %s: Theme author name.
					$theme_version_string       = sprintf( __( 'By %s', 'health-check' ), $theme_author );
					$theme_version_string_debug = sprintf( 'author: %s, version: (undefined)', $theme_author );
				}

				if ( ! empty( $theme_version ) ) {
					// translators: %s: Theme version number.
					$theme_version_string       = sprintf( __( 'Version %s', 'health-check' ), $theme_version );
					$theme_version_string_debug = sprintf( 'author: (undefined), version: %s', $theme_version );
				}
			}

			if ( array_key_exists( $theme_slug, $theme_updates ) ) {
				// translators: %s: Latest theme version number.
				$theme_version_string       .= ' ' . sprintf( __( '(Latest version: %s)', 'health-check' ), $theme_updates[ $theme_slug ]->update['new_version'] );
				$theme_version_string_debug .= sprintf( ' (latest version: %s)', $theme_updates[ $theme_slug ]->update['new_version'] );
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$info['wp-themes-inactive']['fields'][ sanitize_text_field( $theme->Name ) ] = array(
				'label' => sprintf(
					// translators: 1: Theme name. 2: Theme slug.
					__( '%1$s (%2$s)', 'health-check' ),
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$theme->Name,
					$theme_slug
				),
				'value' => $theme_version_string,
				'debug' => $theme_version_string_debug,
			);
		}

		// Add more filesystem checks
		if ( defined( 'WPMU_PLUGIN_DIR' ) && is_dir( WPMU_PLUGIN_DIR ) ) {
			$is_writable_wpmu_plugin_dir = wp_is_writable( WPMU_PLUGIN_DIR );

			$info['wp-filesystem']['fields']['mu-plugins'] = array(
				'label' => __( 'The must use plugins directory', 'health-check' ),
				'value' => ( $is_writable_wpmu_plugin_dir ? __( 'Writable', 'health-check' ) : __( 'Not writable', 'health-check' ) ),
				'debug' => ( $is_writable_wpmu_plugin_dir ? 'writable' : 'not writable' ),
			);
		}

		/**
		 * Add or modify the debug information.
		 *
		 * Plugin or themes may wish to introduce their own debug information without creating additional admin pages
		 * they can utilize this filter to introduce their own sections or add more data to existing sections.
		 *
		 * Array keys for sections added by core are all prefixed with `wp-`, plugins and themes should use their own slug as
		 * a prefix, both for consistency as well as avoiding key collisions. Note that the array keys are used as labels
		 * for the copied data.
		 *
		 * All strings are expected to be plain text except $description that can contain inline HTML tags (see below).
		 *
		 * @since 5.2.0
		 *
		 * @param array $args {
		 *     The debug information to be added to the core information page.
		 *
		 *     This is an associative multi-dimensional array, up to three levels deep. The topmost array holds the sections.
		 *     Each section has a `$fields` associative array (see below), and each `$value` in `$fields` can be
		 *     another associative array of name/value pairs when there is more structured data to display.
		 *
		 *     @type string  $label        The title for this section of the debug output.
		 *     @type string  $description  Optional. A description for your information section which may contain basic HTML
		 *                                 markup, inline tags only as it is outputted in a paragraph.
		 *     @type boolean $show_count   Optional. If set to `true` the amount of fields will be included in the title for
		 *                                 this section.
		 *     @type boolean $private      Optional. If set to `true` the section and all associated fields will be excluded
		 *                                 from the copied data.
		 *     @type array   $fields {
		 *         An associative array containing the data to be displayed.
		 *
		 *         @type string  $label    The label for this piece of information.
		 *         @type string  $value    The output that is displayed for this field. Text should be translated. Can be
		 *                                 an associative array that is displayed as name/value pairs.
		 *         @type string  $debug    Optional. The output that is used for this field when the user copies the data.
		 *                                 It should be more concise and not translated. If not set, the content of `$value` is used.
		 *                                 Note that the array keys are used as labels for the copied data.
		 *         @type boolean $private  Optional. If set to `true` the field will not be included in the copied data
		 *                                 allowing you to show, for example, API keys here.
		 *     }
		 * }
		 */
		$info = apply_filters( 'debug_information', $info );

		return $info;
	}

	/**
	 * Format the information gathered for debugging, in a manner suitable for copying to a forum or support ticket.
	 *
	 * @since 5.2.0
	 *
	 * @param array $info_array Information gathered from the `WP_Debug_Data::debug_data` function.
	 * @param string $type      The data type to return, either 'info' or 'debug'.
	 * @return string The formatted data.
	 */
	public static function format( $info_array, $type ) {
		$return = "`\n";

		foreach ( $info_array as $section => $details ) {
			// Skip this section if there are no fields, or the section has been declared as private.
			if ( empty( $details['fields'] ) || ( isset( $details['private'] ) && $details['private'] ) ) {
				continue;
			}

			$section_label = 'debug' === $type ? $section : $details['label'];

			$return .= sprintf(
				"### %s%s ###\n\n",
				$section_label,
				( isset( $details['show_count'] ) && $details['show_count'] ? sprintf( ' (%d)', count( $details['fields'] ) ) : '' )
			);

			foreach ( $details['fields'] as $field_name => $field ) {
				if ( isset( $field['private'] ) && true === $field['private'] ) {
					continue;
				}

				if ( 'debug' === $type && isset( $field['debug'] ) ) {
					$debug_data = $field['debug'];
				} else {
					$debug_data = $field['value'];
				}

				// Can be array, one level deep only.
				if ( is_array( $debug_data ) ) {
					$value = '';

					foreach ( $debug_data as $sub_field_name => $sub_field_value ) {
						$value .= sprintf( "\n\t%s: %s", $sub_field_name, $sub_field_value );
					}
				} elseif ( is_bool( $debug_data ) ) {
					$value = $debug_data ? 'true' : 'false';
				} elseif ( empty( $debug_data ) && '0' !== $debug_data ) {
					$value = 'undefined';
				} else {
					$value = $debug_data;
				}

				if ( 'debug' === $type ) {
					$label = $field_name;
				} else {
					$label = $field['label'];
				}

				$return .= sprintf( "%s: %s\n", $label, $value );
			}

			$return .= "\n";
		}

		$return .= '`';

		return $return;
	}

	/**
	 * Fetch the total size of all the database tables for the active database user.
	 *
	 * @since 5.2.0
	 *
	 * @return int The size of the database, in bytes.
	 */
	public static function get_database_size() {
		global $wpdb;
		$size = 0;
		$rows = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );

		if ( $wpdb->num_rows > 0 ) {
			foreach ( $rows as $row ) {
				$size += $row['Data_length'] + $row['Index_length'];
			}
		}

		return (int) $size;
	}

	public static function ajax_get_sizes() {
		check_ajax_referer( 'health-check-site-status-result' );

		if ( ! current_user_can( 'view_site_health_checks' ) || is_multisite() ) {
			wp_send_json_error();
		}

		$sizes_data = Health_Check_Debug_Data::get_sizes();
		$all_sizes  = array( 'raw' => 0 );

		foreach ( $sizes_data as $name => $value ) {
			$name = sanitize_text_field( $name );
			$data = array();

			if ( isset( $value['size'] ) ) {
				if ( is_string( $value['size'] ) ) {
					$data['size'] = sanitize_text_field( $value['size'] );
				} else {
					$data['size'] = (int) $value['size'];
				}
			}

			if ( isset( $value['debug'] ) ) {
				if ( is_string( $value['debug'] ) ) {
					$data['debug'] = sanitize_text_field( $value['debug'] );
				} else {
					$data['debug'] = (int) $value['debug'];
				}
			}

			if ( ! empty( $value['raw'] ) ) {
				$data['raw'] = (int) $value['raw'];
			}

			$all_sizes[ $name ] = $data;
		}

		if ( isset( $all_sizes['total_size']['debug'] ) && 'not available' === $all_sizes['total_size']['debug'] ) {
			wp_send_json_error( $all_sizes );
		}

		wp_send_json_success( $all_sizes );
	}

	/**
	 * Fetch the sizes of the WordPress directories: `wordpress` (ABSPATH), `plugins`, `themes`, and `uploads`.
	 * Intended to supplement the array returned by `WP_Debug_Data::debug_data()`.
	 *
	 * @since 5.2.0
	 *
	 * @return array The sizes of the directories, also the database size and total installation size.
	 */
	public static function get_sizes() {
		$size_db    = self::get_database_size();
		$upload_dir = wp_get_upload_dir();

		/*
		 * We will be using the PHP max execution time to prevent the size calculations
		 * from causing a timeout. The default value is 30 seconds, and some
		 * hosts do not allow you to read configuration values.
		 */
		if ( function_exists( 'ini_get' ) ) {
			$max_execution_time = ini_get( 'max_execution_time' );
		}

		// The max_execution_time defaults to 0 when PHP runs from cli.
		// We still want to limit it below.
		if ( empty( $max_execution_time ) ) {
			$max_execution_time = 30;
		}

		if ( $max_execution_time > 20 ) {
			// If the max_execution_time is set to lower than 20 seconds, reduce it a bit to prevent
			// edge-case timeouts that may happen after the size loop has finished running.
			$max_execution_time -= 2;
		}

		if ( ! defined( 'WP_START_TIMESTAMP' ) ) {
			global $timestart;
			if ( version_compare( phpversion(), '5.4.0', '>=' ) && isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ) {
				define( 'WP_START_TIMESTAMP', $_SERVER['REQUEST_TIME_FLOAT'] );
			} else {
				define( 'WP_START_TIMESTAMP', $timestart );
			}
		}

		// Go through the various installation directories and calculate their sizes.
		// No trailing slashes.
		$paths = array(
			'wordpress_size' => untrailingslashit( ABSPATH ),
			'themes_size'    => get_theme_root(),
			'plugins_size'   => WP_PLUGIN_DIR,
			'uploads_size'   => $upload_dir['basedir'],
		);

		$exclude = $paths;
		unset( $exclude['wordpress_size'] );
		$exclude = array_values( $exclude );

		$size_total = 0;
		$all_sizes  = array();

		// Loop over all the directories we want to gather the sizes for.
		foreach ( $paths as $name => $path ) {
			$dir_size = null; // Default to timeout.
			$results  = array(
				'path' => $path,
				'raw'  => 0,
			);

			if ( microtime( true ) - WP_START_TIMESTAMP < $max_execution_time ) {
				if ( 'wordpress_size' === $name ) {
					if ( version_compare( get_bloginfo( 'version' ), '5.2.0', '<' ) ) {
						$dir_size = Health_Check_Debug_Data::recurse_dirsize( $path, $exclude, $max_execution_time );
					} else {
						$dir_size = recurse_dirsize( $path, $exclude, $max_execution_time );
					}
				} else {
					if ( version_compare( get_bloginfo( 'version' ), '5.2.0', '<' ) ) {
						$dir_size = Health_Check_Debug_Data::recurse_dirsize( $path, null, $max_execution_time );
					} else {
						$dir_size = recurse_dirsize( $path, null, $max_execution_time );
					}
				}
			}

			if ( false === $dir_size ) {
				// Error reading.
				$results['size']  = __( 'The size cannot be calculated. The directory is not accessible. Usually caused by invalid permissions.', 'health-check' );
				$results['debug'] = 'not accessible';

				// Stop total size calculation.
				$size_total = null;
			} elseif ( null === $dir_size ) {
				// Timeout.
				$results['size']  = __( 'The directory size calculation has timed out. Usually caused by a very large number of sub-directories and files.', 'health-check' );
				$results['debug'] = 'timeout while calculating size';

				// Stop total size calculation.
				$size_total = null;
			} else {
				if ( null !== $size_total ) {
					$size_total += $dir_size;
				}

				$results['raw']   = $dir_size;
				$results['size']  = size_format( $dir_size, 2 );
				$results['debug'] = $results['size'] . " ({$dir_size} bytes)";
			}

			$all_sizes[ $name ] = $results;
		}

		if ( $size_db > 0 ) {
			$database_size = size_format( $size_db, 2 );

			$all_sizes['database_size'] = array(
				'raw'   => $size_db,
				'size'  => $database_size,
				'debug' => $database_size . " ({$size_db} bytes)",
			);
		} else {
			$all_sizes['database_size'] = array(
				'size'  => __( 'Not available', 'health-check' ),
				'debug' => 'not available',
			);
		}

		if ( null !== $size_total && $size_db > 0 ) {
			$total_size    = $size_total + $size_db;
			$total_size_mb = size_format( $total_size, 2 );

			$all_sizes['total_size'] = array(
				'raw'   => $total_size,
				'size'  => $total_size_mb,
				'debug' => $total_size_mb . " ({$total_size} bytes)",
			);
		} else {
			$all_sizes['total_size'] = array(
				'size'  => __( 'Total size is not available. Some errors were encountered when determining the size of your installation.', 'health-check' ),
				'debug' => 'not available',
			);
		}

		return $all_sizes;
	}

	/**
	 * Fallback function for directory size calculation on sites running WordPress <5.2
	 *
	 * @param string $directory       Full path of a directory.
	 * @param string|array $exclude   Optional. Full path of a subdirectory to exclude from the total, or array of paths.
	 *                                Expected without trailing slash(es).
	 * @param int $max_execution_time Maximum time to run before giving up. In seconds.
	 *                                The timeout is global and is measured from the moment WordPress started to load.
	 * @return int|false|null Size in bytes if a valid directory. False if not. Null if timeout.
	 */
	static function recurse_dirsize( $directory, $exclude = null, $max_execution_time = null ) {
		$size = 0;

		$directory = untrailingslashit( $directory );

		if ( ! file_exists( $directory ) || ! is_dir( $directory ) || ! is_readable( $directory ) ) {
			return false;
		}

		if (
			( is_string( $exclude ) && $directory === $exclude ) ||
			( is_array( $exclude ) && in_array( $directory, $exclude, true ) )
		) {
			return false;
		}

		if ( null === $max_execution_time ) {
			// Keep the previous behavior but attempt to prevent fatal errors from timeout if possible.
			if ( function_exists( 'ini_get' ) ) {
				$max_execution_time = ini_get( 'max_execution_time' );
			} else {
				// Disable...
				$max_execution_time = 0;
			}

			// Leave 1 second "buffer" for other operations if $max_execution_time has reasonable value.
			if ( $max_execution_time > 10 ) {
				$max_execution_time -= 1;
			}
		}

		$handle = opendir( $directory );
		if ( $handle ) {
			while ( ( $file = readdir( $handle ) ) !== false ) {
				$path = $directory . '/' . $file;
				if ( '.' != $file && '..' != $file ) {
					if ( is_file( $path ) ) {
						$size += filesize( $path );
					} elseif ( is_dir( $path ) ) {
						$handlesize = Health_Check_Debug_Data::recurse_dirsize( $path, $exclude, $max_execution_time );
						if ( $handlesize > 0 ) {
							$size += $handlesize;
						}
					}

					if ( $max_execution_time > 0 && microtime( true ) - WP_START_TIMESTAMP > $max_execution_time ) {
						// Time exceeded. Give up instead of risking a fatal timeout.
						$size = null;
						break;
					}
				}
			}
			closedir( $handle );
		}
		return $size;
	}
}
