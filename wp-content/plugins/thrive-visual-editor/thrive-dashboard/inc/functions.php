<?php
/**
 * Holds different helper functions
 * User: Danut
 * Date: 12/8/2015
 * Time: 5:31 PM
 */

/**
 * Main Dashboard section
 *
 * @includes dashboard.phtml template
 */
function tve_dash_section() {
	$products = tve_dash_get_products();

	$thrive_features = tve_dash_get_features();

	require_once TVE_DASH_PATH . '/templates/sections/dashboard.phtml';
}

/**
 * Licence Manager section
 *
 * @includes licence_manager.phtml template
 */
function tve_dash_license_manager_section() {
	$products = tve_dash_get_products( false );

	$returnUrl = esc_url( empty( $_REQUEST['return'] ) ? '' : $_REQUEST['return'] );

	/**
	 * Filter products to only active once
	 *
	 * @var $product TVE_Dash_Product_Abstract
	 */
	foreach ( $products as $key => $product ) {
		if ( ! $product->isActivated() ) {
			unset( $products[ $key ] );
		}
	}

	require_once TVE_DASH_PATH . '/templates/sections/license_manager.phtml';
}

/**
 * Get all settings for the general settings view.
 * Uses 'tve_dash_general_settings_filter' filter if other plugins want to add their settings
 *
 * @return array|mixed
 */
function tve_dash_get_general_settings() {
	$settings = array(
		array(
			'name'         => 'tve_social_fb_app_id',
			'id'           => 'tve_social_fb_app_id',
			'class'        => 'tvd-validate tve_social_fb_app_id',
			'data-success' => 'The App ID provided is valid',
			'data-error'   => 'The App ID provided is invalid',
			'label'        => 'Facebook App ID',
			'description'  => __( 'Facebook ID that will be used in our apps.', TVE_DASH_TRANSLATE_DOMAIN ),
			'value'        => get_option( 'tve_social_fb_app_id', '' ),
			'type'         => 'text',
			'multiple'     => false,
		),
		array(
			'name'         => 'tve_comments_facebook_admins',
			'id'           => 'tve_comments_facebook_admins',
			'class'        => 'tvd-validate tve_comments_facebook_admins',
			'data-success' => '',
			'data-error'   => 'This field can not be empty',
			'label'        => 'Facebook Admins',
			'description'  => __( 'Admins that will moderate the comments', TVE_DASH_TRANSLATE_DOMAIN ),
			'value'        => get_option( 'tve_comments_facebook_admins', '' ),
			'type'         => 'text',
			'multiple'     => true,
		),
		array(
			'name'         => 'tve_comments_disqus_shortname',
			'id'           => 'tve_comments_disqus_shortname',
			'class'        => 'tvd-validate tve_comments_disqus_shortname',
			'data-success' => '',
			'data-error'   => 'This field can not be empty',
			'label'        => 'Disqus forum name',
			'description'  => __( 'Your forum name is part of the address that you login to "http://xxxxxxxx.disqus.com" - the xxxxxxx is your shortname.  For example, with this URL: https://hairfreelife.disqus.com/ the shortname is "hairfreelife', TVE_DASH_TRANSLATE_DOMAIN ),
			'value'        => get_option( 'tve_comments_disqus_shortname', '' ),
			'type'         => 'text',
			'multiple'     => false,
		),
		array(
			'name'     => 'tve_google_fonts_disable_api_call',
			'id'       => 'tve_google_fonts_disable_api_call',
			'value'    => get_option( 'tve_google_fonts_disable_api_call', '' ),
			'type'     => 'checkbox',
			'multiple' => false,
		),
	);
	$settings = apply_filters( 'tve_dash_general_settings_filter', $settings );

	return $settings;
}

/**
 * General Settings section
 *
 * @includes general_settings.phtml template
 */
function tve_dash_general_settings_section() {
	$affiliate_links = tve_dash_get_affiliate_links();
	$settings        = tve_dash_get_general_settings();
	/* text, radio, checkbox, password */
	$accepted_settings = array( 'text', 'checkbox' );
	require_once TVE_DASH_PATH . '/templates/settings/general_settings.phtml';
}

/**
 * Thrive UI toolkit - should contain a list of all UI components used in Thrive admin UI
 *
 * @see templates/ui.phtml
 */
function tve_dash_ui_toolkit() {
	require TVE_DASH_PATH . '/templates/ui.phtml';
}

/**
 * wrapper over the wp_enqueue_script functions
 * it will add the version
 *
 * @param        $handle
 * @param string $src
 * @param array  $deps
 * @param bool   $ver
 * @param bool   $in_footer
 */
function tve_dash_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
	if ( $ver === false ) {
		$ver = TVE_DASH_VERSION;
	}
	if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
		$src = preg_replace( '/\.min.js$/', '.js', $src );
	}
	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * Display Access manager page
 */
function tve_dash_access_manager_main_page() {
	TVD_AM::instance()->display_page();
}

/**
 * Wrapper over the wp enqueue_style function
 * It will add the version
 *
 * @param       $handle
 * @param       $src
 * @param array $deps
 * @param bool  $ver
 * @param       $media
 */
function tve_dash_enqueue_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
	if ( $ver === false ) {
		$ver = TVE_DASH_VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * Returns the products to be displayed on Main Dashboard Section
 *
 * @calls apply_filters('tve_dash_installed_products')
 *
 * @param $check_rights to capability or not
 *
 * @return array
 */
function tve_dash_get_products( $check_rights = true ) {
	$return = array();

	foreach ( apply_filters( 'tve_dash_installed_products', array() ) as $_product ) {
		/** @var $_product TVE_Dash_Product_Abstract */
		if ( $check_rights && ! $_product->has_access() && $_product->getType() !== 'theme' ) {
			continue;
		}
		$return[ $_product->getTag() ] = $_product;
	}

	return $return;
}

/**
 * get a list of all available thrive features
 *
 * uses the tve_dash_features filter to populate the array with pre-existing functionalities
 * the filter should just add one of the keys to the array:
 *
 *      api_connections
 *      font_manager
 *      icon_manager
 *      general_settings
 *
 * @return array
 */
function tve_dash_get_features() {

	if ( ! current_user_can( TVE_DASH_CAPABILITY ) ) {
		return array();
	}

	$thrive_features = array(
		'access_manager'   => array(
			'icon'        => 'tvd-users',
			'title'       => __( 'User Access Manager', TVE_DASH_TRANSLATE_DOMAIN ),
			'description' => __( 'Access Permissions for Thrive Products', TVE_DASH_TRANSLATE_DOMAIN ),
			'btn_link'    => add_query_arg( 'page', 'tve_dash_access_manager', admin_url( 'admin.php' ) ),
			'btn_text'    => __( "Manage Access", TVE_DASH_TRANSLATE_DOMAIN ),
		),
		'api_connections'  => array(
			'icon'        => 'tvd-icon-exchange',
			'title'       => __( "API Connections", TVE_DASH_TRANSLATE_DOMAIN ),
			'description' => __( "Connect to your email marketing system, reCaptcha, email delivery services & more.", TVE_DASH_TRANSLATE_DOMAIN ),
			'btn_link'    => add_query_arg( 'page', 'tve_dash_api_connect', admin_url( 'admin.php' ) ),
			'btn_text'    => __( "Manage Connections", TVE_DASH_TRANSLATE_DOMAIN ),
		),
		'font_manager'     => array(
			'icon'        => 'tvd-icon-font',
			'title'       => __( "Custom Fonts", TVE_DASH_TRANSLATE_DOMAIN ),
			'description' => __( "Add & edit Google Fonts and other custom fonts to use in your Thrive products.", TVE_DASH_TRANSLATE_DOMAIN ),
			'btn_link'    => add_query_arg( 'page', 'tve_dash_font_manager', admin_url( 'admin.php' ) ),
			'btn_text'    => __( "Manage Fonts", TVE_DASH_TRANSLATE_DOMAIN ),
		),
		'icon_manager'     => array(
			'icon'        => 'tvd-icon-rocket',
			'title'       => __( "Retina Icons", TVE_DASH_TRANSLATE_DOMAIN ),
			'description' => __( "Add & edit fully scalable icons with our font icon manager.", TVE_DASH_TRANSLATE_DOMAIN ),
			'btn_link'    => add_query_arg( 'page', 'tve_dash_icon_manager', admin_url( 'admin.php' ) ),
			'btn_text'    => __( "Manage Icons", TVE_DASH_TRANSLATE_DOMAIN ),
		),
		'general_settings' => array(
			'icon'        => 'tvd-icon-cogs',
			'title'       => __( "General Settings", TVE_DASH_TRANSLATE_DOMAIN ),
			'description' => __( "Shared settings between multiple themes and plugins.", TVE_DASH_TRANSLATE_DOMAIN ),
			'btn_link'    => add_query_arg( 'page', 'tve_dash_general_settings_section', admin_url( 'admin.php' ) ),
			'btn_text'    => __( "Manage Settings", TVE_DASH_TRANSLATE_DOMAIN ),
		),
		'script_manager'   => array(
			'icon'        => 'tvd-nm-icon-code',
			'title'       => __( 'Analytics & Scripts', TVE_DASH_TRANSLATE_DOMAIN ),
			'description' => __( 'Add & edit scripts on your website.', TVE_DASH_TRANSLATE_DOMAIN ),
			'btn_link'    => add_query_arg( 'page', 'tve_dash_script_manager', admin_url( 'admin.php' ) ),
			'btn_text'    => __( 'Manage Scripts', TVE_DASH_TRANSLATE_DOMAIN ),
		),
	);

	/**
	 * For now, the font manager is available only for the users who have a custom font loaded.
	 */
	$custom_fonts = json_decode( get_option( 'thrive_font_manager_options' ), true );
	if ( empty( $custom_fonts ) ) {
		unset( $thrive_features['font_manager'] );
	}

	$enabled         = apply_filters( 'tve_dash_features', array() );
	$thrive_features = apply_filters( 'tve_dash_filter_features', $thrive_features );

	/**
	 * Thrive dashboard admin feature is only enabled for super admins
	 */
	if ( is_super_admin() ) {
		$enabled['access_manager'] = true;
	}

	$thrive_features = array_intersect_key( $thrive_features, array_filter( $enabled ) );

	return $thrive_features;
}

/**
 * Check if the default capability for admin & editor is set otherwise we need to set it
 */
function tve_dash_check_default_cap() {
	foreach ( tve_dash_get_products( false ) as $_product ) {
		/** @var $_product TVE_Dash_Product_Abstract */
		$_product->check_default_cap();
	}

	$admin = get_role( 'administrator' );
	if ( $admin && ( ! $admin->has_cap( TVE_DASH_CAPABILITY ) || ! $admin->has_cap( TVE_DASH_EDIT_CPT_CAPABILITY ) ) ) {
		$admin->add_cap( TVE_DASH_CAPABILITY );
		$admin->add_cap( TVE_DASH_EDIT_CPT_CAPABILITY );
	}

	if ( ! get_option( 'tve_dash_default_cap_set' ) ) {
		$editor = get_role( 'editor' );
		if ( $editor ) {
			$editor->add_cap( TVE_DASH_CAPABILITY );
			$editor->add_cap( TVE_DASH_EDIT_CPT_CAPABILITY );
		}
		add_option( 'tve_dash_default_cap_set', true );
	}
}

/**
 * SPL loader
 *
 * @param $className
 *
 * @return bool
 */
function tve_dash_autoloader( $className ) {
	$namespace = 'TVE_Dash_';
	if ( strpos( $className, $namespace ) !== 0 ) {
		return false;
	}

	$basedir = rtrim( dirname( dirname( __FILE__ ) ), '/\\' ) . '/classes/';

	return tve_dash_autoload( $basedir, str_replace( $namespace, '', $className ) );
}

/**
 * Loads the class based on $path and $className
 *
 * @param $path
 * @param $className
 *
 * @return bool
 */
function tve_dash_autoload( $path, $className ) {
	$parts = explode( '_', $className );
	if ( empty( $parts ) ) {
		return false;
	}

	$filename = array_pop( $parts );

	foreach ( $parts as $part ) {
		$part = str_replace( array( 'Model', 'View' ), array( 'Models', 'Views' ), $part );
		$path .= $part . '/';
	}

	$path .= $filename . '.php';

	if ( ! file_exists( $path ) ) {
		return false;
	}

	require_once $path;
}

/**
 *
 * transform any url into a protocol-independent url
 *
 * @param string $raw_url
 *
 * @return string
 */
function tve_dash_url_no_protocol( $raw_url ) {
	return preg_replace( '#http(s)?://#', '//', $raw_url );
}

/**
 * check whether or not the user has a caching plugin installed and try to detect the actual plugin being used
 *
 * @return bool|string false if there is no known caching plugin installed, or string the name of installed caching plugin
 */
function tve_dash_detect_cache_plugin() {
	$known_plugins = array(
		'wp-super-cache/wp-cache.php',
		'w3-total-cache/w3-total-cache.php',
		'wp-rocket/wp-rocket.php',
		'wp-fastest-cache/wpFastestCache.php',
		'litespeed-cache/litespeed-cache.php',
	);
	$known_plugins = apply_filters( 'tve_dash_cache_known_plugins', $known_plugins );

	if ( ! is_array( $known_plugins ) || empty( $known_plugins ) ) {
		return false;
	}

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	foreach ( $known_plugins as $plugin_file ) {
		if ( is_plugin_active( $plugin_file ) ) {
			return dirname( $plugin_file );
		}
	}

	return false;
}

/**
 * try to automatically prune (clear) the cache if the user has a known caching plugin installed
 *
 * @param string $cache_plugin
 *
 * @return bool true on success, false on failure
 */
function tve_dash_cache_plugin_clear( $cache_plugin ) {
	$known_callbacks = array(
		'wp-super-cache'   => 'wp_cache_clear_cache',
		'w3-total-cache'   => 'w3tc_pgcache_flush',
		'wp-rocket'        => 'rocket_clean_domain',
		'wp-fastest-cache' => 'deleteCssAndJsCache',
		'litespeed-cache'  => array( 'LiteSpeed_Cache_Purge', 'purge_all' ),
	);

	if ( ! isset( $known_callbacks[ $cache_plugin ] ) ) {
		$known_callbacks[ $cache_plugin ] = apply_filters( 'tve_dash_cache_clear_callback', '', $cache_plugin );
	}

	if ( isset( $known_callbacks[ $cache_plugin ] ) ) {
		$fn = $known_callbacks[ $cache_plugin ];
		if ( is_array( $fn ) ) {
			if ( ! class_exists( $fn[0], false ) || ! method_exists( $fn[0], $fn[1] ) ) {
				return false;
			}
		} elseif ( ! function_exists( $fn ) ) {
			return false;
		}
		call_user_func( $fn );

		return true;
	}

	return false;
}

function tve_dash_get_error_log_entries( $order_by = 'date', $order = 'DESC', $per_page = 10, $current_page = 1 ) {

	/** @var $wpdb wpdb */
	global $wpdb;

	$table_name = $wpdb->prefix . 'tcb_api_error_log';
	$sql        = "SELECT * FROM {$table_name}";
	$params     = array();

	$sql .= ' ORDER BY `%1s` %1s';

	$params[] = $order_by;
	$params[] = $order;

	$items_sql = $wpdb->prepare( $sql, $params );

	//get total items
	$data['settings']['items'] = $wpdb->query( $items_sql );

	$data['settings']['pages'] = ceil( $data['settings']['items'] / $per_page );

	//calculate the offset from where to begin the query
	$offset = ( $current_page - 1 ) * $per_page;

	$sql .= ' LIMIT %d,%d';

	$params[] = $offset;
	$params[] = $per_page;

	$models = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

	$available_apis = Thrive_Dash_List_Manager::getAvailableAPIs( false, array() );

	foreach ( $models as $key => $entry ) {
		$unserialized_data                   = unserialize( $entry->api_data );
		$models[ $key ]->fields_html         = tve_dash_build_column_api_data( $unserialized_data );
		$models[ $key ]->api_data            = json_encode( $unserialized_data );
		$models[ $key ]->connection_explicit = $available_apis[ $entry->connection ]->getTitle();
		$models[ $key ]->connection_type     = $available_apis[ $entry->connection ]->getType();
	}

	$data['models'] = $models;

	return $data;
}

function tve_dash_build_column_api_data( $data ) {

	$info = '';

	if ( ! empty( $data['email'] ) ) {
		$info .= sprintf(
			'<strong>%s</strong>: %s<br/>',
			__( 'Email', TVE_DASH_TRANSLATE_DOMAIN ),
			sanitize_email( $data['email'] )
		);
	}

	if ( ! empty( $data['email_address'] ) ) {
		$info .= sprintf(
			'<strong>%s</strong>: %s<br/>',
			__( 'Email', TVE_DASH_TRANSLATE_DOMAIN ),
			sanitize_email( $data['email_address'] )
		);
	}

	if ( ! empty( $data['status'] ) ) {
		$info .= sprintf(
			'<strong>%s</strong>: %s<br/>',
			__( 'Status', TVE_DASH_TRANSLATE_DOMAIN ),
			esc_html( $data['status'] )
		);
	}

	// Needs a refactor due to multiple custom fields APIs implementation
	// Mailchimp custom fields err message
	if ( ! empty( $data['merge_fields'] ) ) {
		$info .= '<strong><u>' . __( 'Custom fields', TVE_DASH_TRANSLATE_DOMAIN ) . ':</u></strong><br/>';
		foreach ( (object) $data['merge_fields'] as $field_name => $field_value ) {
			$info .= sprintf( '<strong>%s</strong>: %s', esc_html( $field_name ), esc_html( $field_value ) );
		}
		$info = substr( $info, 0, - 2 );
	}

	// GetResponse custom fields err message
	if ( ! empty( $data['customFieldValues'] ) ) {
		$info .= '<strong><u>' . __( 'Custom fields', TVE_DASH_TRANSLATE_DOMAIN ) . ':</u></strong><br/>';
		foreach ( $data['customFieldValues'] as $field_value ) {
			$field_id         = ! empty( $field_value['customFieldId'] ) ? $field_value['customFieldId'] : '';
			$field_mapped_val = ! empty( $field_value['value'][0] ) ? $field_value['value'][0] : '';
			$info             .= sprintf( '<strong> %s </strong>: %s, ', esc_html( $field_id ), esc_html( $field_mapped_val ) );
		}
		$info = substr( $info, 0, - 2 );
	}

	// Infusionsoft custom fields err message
	if ( ! empty( $data['infusion_custom_fields'] ) && is_array( $data['infusion_custom_fields'] ) ) {

		$info .= '<strong><u>' . __( 'Custom fields', TVE_DASH_TRANSLATE_DOMAIN ) . ':</u></strong><br/>';
		foreach ( $data['infusion_custom_fields'] as $field_name => $field_value ) {
			if ( ! is_string( $field_name ) || ! is_string( $field_value ) ) {
				continue;
			}

			$info .= sprintf( '<strong> %s </strong>: %s, ', esc_html( $field_name ), esc_html( $field_value ) );
		}
	}

	if ( ! empty( $data['name'] ) ) {
		$info .= sprintf(
			'<strong>%s</strong>: %s<br/>',
			__( 'Name', TVE_DASH_TRANSLATE_DOMAIN ),
			esc_html( $data['name'] )
		);
	}

	if ( ! empty( $data['phone'] ) ) {
		$info .= sprintf(
			'<strong>%s</strong>: %s<br/>',
			__( 'Phone', TVE_DASH_TRANSLATE_DOMAIN ),
			esc_html( $data['phone'] )
		);
	}

	return trim( $info );
}

/**
 * Generate secret and set cookie
 *
 * @return mixed|string
 */
function tve_dash_generate_secret() {

	$rand = md5( mt_rand() );

	if ( ! empty( $_COOKIE[ TVE_SECRET ] ) ) {
		$rand = $_COOKIE[ TVE_SECRET ];
	}

	setcookie( TVE_SECRET, $rand, strtotime( '+1 year' ), '/' );

	return $rand;
}

/**
 * Verify secret
 *
 * @param string $secret
 *
 * @return bool
 */
function tve_dash_check_secret( $secret ) {
	if ( empty( $secret ) || empty( $_COOKIE[ TVE_SECRET ] ) || $secret != $_COOKIE[ TVE_SECRET ] ) {
		return false;
	}

	return true;
}

/**
 * Get affiliate options for each allowed product
 *
 * @return array
 */
function tve_dash_get_affiliate_links() {
	$menus = apply_filters( 'tve_dash_admin_product_menu', array() );

	$available_products = array(
		'tva' => array(
			'label'   => __( 'Display "Powered by Thrive Apprentice"' ),
			'checked' => false,
			'tag'     => 'tva',
		),
		'tcm' => array(
			'label'   => __( 'Display "Powered by Thrive Comments"' ),
			'checked' => false,
			'tag'     => 'tcm',
		),
		'tqb' => array(
			'label'   => __( 'Display "Powered by Thrive Quiz Builder"' ),
			'checked' => false,
			'tag'     => 'tqb',
		),
	);
	if ( function_exists( 'thrive_get_theme_options' ) ) {
		$available_products['thrive_theme_admin_options'] = array(
			'label'   => __( 'Display "Powered by Thrive Themes"' ),
			'checked' => false,
			'tag'     => 'thrive_theme_admin_options',
		);
	}
	$allowed_products = array();

	foreach ( $available_products as $key => $product ) {
		if ( array_key_exists( $key, $menus ) ) {
			$option                   = tve_dash_get_product_option( $key );
			$product['checked']       = $option;
			$allowed_products[ $key ] = $product;
		}
	}

	return $allowed_products;
}

/**
 * Set affiliate options for each allowed product
 *
 * @param $product_tag
 *
 * @return string
 */
function tve_dash_get_product_option( $product_tag ) {

	$option = '';
	switch ( $product_tag ) {
		case 'tqb':
			$tqb_settings = tqb_get_option( Thrive_Quiz_Builder::PLUGIN_SETTINGS, tqb_get_default_values( Thrive_Quiz_Builder::PLUGIN_SETTINGS ) );

			$option = $tqb_settings['tqb_promotion_badge'];

			break;

		case 'tcm':
			$tcm_settings = tcms()->tcm_get_settings();
			$option       = $tcm_settings['powered_by'];

			break;

		case 'tva':
			$tva_settings  = TVA_Settings::instance();
			$user_settings = $tva_settings->get_settings();
			$option        = $user_settings['apprentice_label'];

			break;

		case 'thrive_theme_admin_options':
			if ( function_exists( 'thrive_get_theme_options' ) ) {
				$theme_options = thrive_get_theme_options();
				$option        = $theme_options['footer_copyright_links'];
			}

			break;
	}

	return $option;
}

/**
 * Update affiliate options for each allowed product
 *
 * @param $product_tag
 * @param $option
 *
 * @return mixed
 */
function tve_dash_update_product_option( $product_tag, $option ) {
	$option = (int) $option === 1;

	switch ( $product_tag ) {
		case 'tqb':
			$data = array( 'tqb_promotion_badge' => $option );
			tqb_update_option( 'tqb_settings', $data, true );

			break;

		case 'tcm':
			tcah()->tcm_update_option( 'powered_by', $option );

			break;

		case 'tva':
			$tva_settings = get_option( 'tva_template_general_settings', '' );

			$tva_settings['apprentice_label'] = $option;
			update_option( 'tva_template_general_settings', $tva_settings );

			break;

		case 'thrive_theme_admin_options':
			if ( function_exists( 'thrive_get_theme_options' ) ) {
				$theme_options                           = thrive_get_theme_options();
				$theme_options['footer_copyright_links'] = $option;
				update_option( 'thrive_theme_options', $theme_options );
			}

			break;
	}

	return $option;
}

/**
 * Displays an icon using svg format
 *
 * @param string $icon
 * @param bool   $return      whether to return the icon as a string or to output it directly
 * @param string $namespace   (where this icon is used - for 'editor' it will add another prefix to it)
 * @param string $extra_class classes to be added to the svg
 * @param array  $svg_attr    array with extra attributes to add to the <svg> tag
 *
 * @return mixed
 */
function dashboard_icon( $icon, $return = false, $namespace = 'sidebar', $extra_class = '', $svg_attr = array() ) {
	$use = $namespace !== 'sidebar' ? 'tvd-icon-' : 'icon-';

	$extra_attr = '';
	if ( ! empty( $svg_attr ) ) {
		foreach ( $svg_attr as $attr_name => $attr_value ) {
			$extra_attr .= ( $extra_attr ? ' ' : '' ) . $attr_name . '="' . esc_attr( $attr_value ) . '"';
		}
	}

	$html = '<svg class="tvd-icon tvd-icon-' . $icon . ( empty( $extra_class ) ? '' : ' ' . $extra_class ) . '"' . $extra_attr . '><use xlink:href="#' . $use . $icon . '"></use></svg>';

	if ( false !== $return ) {
		return $html;
	}

	echo $html;
}

/**
 * Gets REMOTE IP
 *
 * @return string
 */
function tve_dash_get_ip() {

	foreach (
		array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		) as $key
	) {
		if ( true === array_key_exists( $key, $_SERVER ) ) {
			foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
				$ip = trim( $ip ); // just to be safe

				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {

					// If IPv6 is installed on the server but the fwded IP is IPv4,
					// the HTTP_X_FORWARDED_FOR will be formatted as "::ffff:94.53.216.105" and APIs will not accept it
					return ( strpos( $ip, '::' ) !== false ) ? substr( $ip, strrpos( $ip, ':' ) + 1 ) : $ip;
				}
			}
		}
	}

	// In order for not breaking API IP validation [the following return value means none of the above methods available, mostly this is the localhost case]
	return '127.0.0.1';
}

/**
 * Get current user data
 *
 * @return array
 */
function tve_current_user_data() {
	$current_user = wp_get_current_user();
	$user_data    = array();
	if ( ! empty( $current_user->data ) && ! empty( $current_user->data->ID ) ) {
		$user_meta = get_user_meta( $current_user->data->ID );
		$user_data = array(
			'user_email'   => $current_user->data->user_email,
			'username'     => $current_user->data->user_login,
			'nickname'     => implode( '', isset( $user_meta['nickname'] ) ? $user_meta['nickname'] : array() ),
			'first_name'   => implode( '', isset( $user_meta['first_name'] ) ? $user_meta['first_name'] : array() ),
			'last_name'    => implode( '', isset( $user_meta['last_name'] ) ? $user_meta['last_name'] : array() ),
			'role'         => implode( '', $current_user->roles ),
			'display_name' => $current_user->data->display_name,
			'ip'           => tve_dash_get_ip(),
			'registered'   => $current_user->data->user_registered,
			'id'           => $current_user->data->ID,
			'edit_url'     => get_edit_profile_url( $current_user->ID ),
		);
	}

	return $user_data;
}

/**
 * Returns the current user details
 *
 * Used in hooks that are sent to 3rd party developers
 *
 * @return array|null
 */
function tvd_get_current_user_details() {
	$user_id = get_current_user_id();

	if ( ! empty( $user_id ) ) {
		$current_user_data = tve_current_user_data();
		$user_meta         = get_user_meta( $current_user_data['id'] );

		$comments_number = get_comments( array(
			'type'    => '',
			'user_id' => '1',
			'count'   => true,
		) );

		$user_details = array(
			'user_id'          => $current_user_data['id'],
			'last_logged_in'   => ( isset( $user_meta['tve_last_login'] ) && is_array( $user_meta['tve_last_login'] ) ) ? date( 'Y-m-d H:i:s', (int) $user_meta['tve_last_login'][0] ) : '',
			'last_updated'     => ( isset( $user_meta['last_updated'] ) && is_array( $user_meta['last_updated'] ) ) ? date( 'Y-m-d H:i:s', (int) $user_meta['last_updated'][0] ) : '',
			'registered'       => $current_user_data['registered'],
			'username'         => $current_user_data['username'],
			'membership_level' => $current_user_data['role'],
			'email'            => $current_user_data['user_email'],
			'ip_address'       => $current_user_data['ip'],
			'user_agent'       => ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? esc_html( $_SERVER['HTTP_USER_AGENT'] ) : '',
			'comments'         => $comments_number,
		);

		return apply_filters( 'thrive_dashboard_extra_user_data', $user_details );
	}

	return null;
}

/**
 * Computes the user login form data needed for the login hook
 *
 * @param string $status Is a flag that is being sent to the `thrive_core_user_login` hook
 *
 * @return array
 */
function tvd_get_login_form_data( $status ) {

	if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
		return array();
	}

	$tmp        = explode( '?', filter_var( $_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL ) );
	$login_page = reset( $tmp );

	if ( ! in_array( $status, array( 'success', 'fail' ) ) ) {
		return array();
	}

	return array(
		'login_page'     => $login_page,
		'login_redirect' => ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '',
		'login_time'     => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'result'         => $status,
	);
}

/**
 * Verify if the data is bas64 encoded
 *
 * @param $data
 *
 * @return bool
 */
function tve_dash_is_bas64_encoded( $data ) {

	$return = false;

	if ( base64_encode( base64_decode( $data, true ) ) === $data ) {
		$return = true;
	}

	return $return;
}

/**
 * Check if debugging is on
 *
 * @return bool
 */
function tve_dash_is_debug_on() {

	return defined( 'TVE_DEBUG' ) && TVE_DEBUG;
}

/**
 * Global recursive function for sanitizing data,
 * by using custom class methods or wp standard sanitize functions,
 * sent in $callback param
 *
 * @param mixed        $data     { accepts array|object|string }
 * @param string|array $callback { callback function: (string) 'function_name' / (array) [class_name, method_name] }
 *
 * @return mixed
 */
function tve_sanitize_data_recursive( $data, $callback = 'sanitize_text_field' ) {

	return is_callable( $callback ) ? map_deep( $data, $callback ) : $data;
}

/**
 * Filter forms before showing them to the users
 */
function tve_filter_intrusive_forms( $product, $forms ) {

	/**
	 * Enable all the products to hook here and filter the forms that will be showed to the users
	 *
	 * @param array  $forms   - an array of items that will show up
	 * @param string $product - the product from which the items originated
	 */
	return apply_filters( 'tve_intrusive_forms', $forms, $product );
}

/**
 * Json checker
 *
 * @param $string
 *
 * @return bool
 */
function tve_is_json_encoded( $string ) {

	return is_string( $string ) && is_array( json_decode( $string, true ) ) && ( json_last_error() == JSON_ERROR_NONE ) ? true : false;
}

/**
 * Does what it says
 *
 * @param string $post_type
 *
 * @return string
 */
function tvd_get_post_type_label( $post_type = '' ) {

	if ( empty( $post_type ) ) {
		$post_type = get_post_type();
	}

	$post_type_object = get_post_type_object( $post_type );

	$post_type_label = ucfirst( $post_type );

	if ( $post_type_object !== null ) {
		$post_type_label = empty( $post_type_object->labels->singular_name ) ? $post_type_object->label : $post_type_object->labels->singular_name;
	}

	return $post_type_label;
}

/**
 * Sanitize a string by removing script and style nodes. Used for sanitizing content, where admin can add html code
 *
 * @param string $string string to be escaped
 *
 * @return string
 *
 */
function tvd_remove_script_tag( $string ) {
	return preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
}

/**
 * Remove our transients
 */
function tvd_reset_transient() {
	global $wpdb;

	$wpdb->query(
		"UPDATE $wpdb->options SET `option_value`='1' WHERE 
						`option_name` LIKE '%transient_timeout_tcb%' OR 
						`option_name` LIKE '%transient_timeout_tar%' OR 
						`option_name` LIKE '%transient_timeout_ttb%'"
	);
}
