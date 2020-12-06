<?php
/**
 * general functions used all across TCB
 */

use \TCB\inc\helpers\FileUploadConfig;

/**
 * @param string $file optional file path
 *
 * @return string the URL to the /editor/css/ dir
 */
function tve_editor_css( $file = null ) {
	return tve_editor_url() . '/editor/css' . ( null !== $file ? '/' . $file : '' );
}

/**
 * @return string the url to the editor/js folder
 */
function tve_editor_js() {
	return tve_editor_url() . '/editor/js/dist';
}

/**
 * return the absolute path to the plugin folder
 *
 * @param string $file
 *
 * @return string
 */
function tve_editor_path( $file = '' ) {
	return plugin_dir_path( dirname( __FILE__ ) ) . ltrim( $file, '/' );
}

/**
 * get all the style families used by TCB
 *
 * @return array
 */
function tve_get_style_families() {
	return apply_filters( 'tcb_style_families', array(
		'Flat'    => tve_editor_css() . '/thrive_flat.css?ver=' . TVE_VERSION,
		'Classy'  => tve_editor_css() . '/thrive_classy.css?ver=' . TVE_VERSION,
		'Minimal' => tve_editor_css() . '/thrive_minimal.css?ver=' . TVE_VERSION,
	) );
}

/**
 *
 * @return string the absolute url to the landing page templates folder
 */
function tve_landing_page_template_url() {
	return tve_editor_url() . '/landing-page/templates';
}

/**
 * notice to be displayed if license not validated - going to load the styles inline because there are so few lines and not worth an extra server hit.
 */
function tve_license_notice() {
	include dirname( dirname( __FILE__ ) ) . '/inc/license_notice.php';
}

/**
 * register Thrive Architect global settings
 */
function tve_global_options_init() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$plugin_db_version = get_option( 'tve_version' );
	if ( ! $plugin_db_version || $plugin_db_version != TVE_VERSION ) {
		tve_run_plugin_upgrade( $plugin_db_version, TVE_VERSION );
		update_option( 'tve_version', TVE_VERSION );
	}

	/**
	 * Cloud Content Templates - custom post type
	 */
	register_post_type( TCB_CT_POST_TYPE, array(
		'public' => false,
	) );

	/**
	 * File upload shortcodes - stored as custom post types
	 */
	register_post_type( FileUploadConfig::POST_TYPE, array(
		'public' => false,
	) );
}

/**
 * Returns the url for closing the TCB editing screen.
 *
 * If no post id is set then will use native WP functions to get the editing URL for the piece of content that's currently being edited
 *
 * @param bool $post_id
 *
 * @return string
 */
function tcb_get_editor_close_url( $post_id = false ) {
	/**
	 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
	 */
	$admin_ssl = strpos( admin_url(), 'https' ) === 0;
	$post_id   = ( $post_id ) ? $post_id : get_the_ID();

	$editor_link = set_url_scheme( get_permalink( $post_id ) );
	$close_url   = apply_filters( 'tcb_close_url', $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link );

	return $close_url;
}

/**
 * Returns the url for the TCB editing screen.
 *
 * If no post id is set then will use native WP functions to get the editing URL for the piece of content that's currently being edited
 *
 * @param int  $post_id
 * @param bool $main_frame whether or not to get the main frame Editor URL or the child frame one
 *
 * @return string
 */
function tcb_get_editor_url( $post_id = 0, $main_frame = true ) {
	/**
	 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
	 */
	$admin_ssl = strpos( admin_url(), 'https' ) === 0;

	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	/*
     * We need the post to complete the full arguments for the preview_post_link filter
     */
	$params = array(
		TVE_EDITOR_FLAG => 'true',
	);

	if ( $main_frame ) {
		$editor_link      = get_edit_post_link( $post_id, '' );
		$params['action'] = 'architect';
	} else {
		$params[ TVE_FRAME_FLAG ] = wp_create_nonce( TVE_FRAME_FLAG );
		$editor_link              = set_url_scheme( get_permalink( $post_id ) );
		$editor_link              = apply_filters( 'tcb_frame_request_uri', $editor_link );
	}

	$editor_link = add_query_arg( apply_filters( 'tcb_editor_edit_link_query_args', $params, $post_id ), $editor_link );

	return $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link;
}

/**
 * Returns the preview URL for any given post/page
 *
 * If no post id is set then will use native WP functions to get the editing URL for the piece of content that's currently being edited
 *
 * @param bool $post_id
 * @param bool $preview
 *
 * @return string
 */
function tcb_get_preview_url( $post_id = false, $preview = true ) {
	$post_id = ( $post_id ) ? $post_id : get_the_ID();
	/*
     * We need the post to complete the full arguments for the preview_post_link filter
     */
	$post         = get_post( $post_id );
	$preview_link = set_url_scheme( get_permalink( $post_id ) );
	$query_args   = array();

	if ( $preview ) {
		$query_args['preview'] = 'true';
	}

	$preview_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( apply_filters( 'tcb_editor_preview_link_query_args', $query_args, $post_id ), $preview_link ), $post ) );

	return $preview_link;
}

/**
 * Get default edit link for a post (WP edit / a custom dashboard)
 *
 * @param bool $post_id
 *
 * @return string
 */
function tcb_get_default_edit_url( $post_id = false ) {
	$post_id = ( $post_id ) ? $post_id : get_the_ID();

	$post      = get_post( $post_id );
	$edit_link = set_url_scheme( get_edit_post_link( $post_id ) );


	/**
	 * Allows changing the default wp post's edit link
	 * Used for save & return to edit dashboard
	 *
	 * @param string   $edit_link - default wp edit link
	 * @param stdClass $post      - current post
	 */
	$edit_link = esc_url( apply_filters( 'tcb_edit_post_default_url', $edit_link, $post ) );

	return $edit_link;
}

/**
 *
 * checks whether the $post_type is editable using the TCB
 *
 * @param string $post_type
 * @param int    $post_id
 *
 * @return bool true if the post type is editable
 */
function tve_is_post_type_editable( $post_type, $post_id = null ) {
	/* post types that are not editable using the content builder - handled as a blacklist */
	$blacklist_post_types = array(
		'acf-field-group',
		'focus_area',
		'thrive_optin',
		'tvo_shortcode',
		/**
		 * On Cartflows's 'cartflows_flow' posts can't be edited with TAR
		 */
		'cartflows_flow',
	);

	$blacklist_post_types = apply_filters( 'tcb_post_types', $blacklist_post_types );

	if ( isset( $blacklist_post_types['force_whitelist'] ) && is_array( $blacklist_post_types['force_whitelist'] ) ) {
		return in_array( $post_type, $blacklist_post_types['force_whitelist'] );
	}

	if ( in_array( $post_type, $blacklist_post_types ) ) {
		return false;
	}

	if ( $post_id === null ) {
		$post_id = get_the_ID();
	}

	return apply_filters( 'tcb_post_editable', true, $post_type, $post_id );
}

/**
 * Sometimes the only way to make the plugin work with other scripts is by deregistering them on the editor page
 */
function tve_remove_conflicting_scripts() {
	if ( is_editor_page() ) {
		/**  Genesis framework - Media Child theme contains a script that prevents users from being able to close the media library */
		wp_dequeue_script( 'yt-embed' );
		wp_deregister_script( 'yt-embed' );

		/** Member player loads jquery tools which conflicts with jQuery UI */
		wp_dequeue_script( 'mpjquerytools' );
		wp_deregister_script( 'mpjquerytools' );

		/** Solved Conflict with WooCommerce Geolocation setting with cache */
		/** When Geolocation with page cache is enabled scripts are duplicated in the iFrame */
		wp_deregister_script( 'wc-geolocation' );
		wp_dequeue_script( 'wc-geolocation' );

		/* wp 2019 theme expecting to have a .site-branding div in the page */
		wp_deregister_script( 'twentynineteen-touch-navigation' );
		wp_dequeue_script( 'twentynineteen-touch-navigation' );

		/* TAR-5246 - floating preview in editor is not working because of the mm scripts */
		wp_dequeue_script( 'mm-common-core.js' );
		wp_deregister_script( 'mm-common-core.js' );
		wp_dequeue_script( 'mm-preview.js' );
		wp_deregister_script( 'mm-preview.js' );
		wp_dequeue_script( 'membermouse-socialLogin' );
		wp_deregister_script( 'membermouse-socialLogin' );
		wp_dequeue_script( 'inbound-analytics' );
		wp_deregister_script( 'inbound-analytics' );
	}
}

/**
 * Adds TCB editing URL to underneath the post title in the Wordpress post listings view
 *
 * @param $actions
 * @param $page_object
 *
 * @return mixed
 */
function thrive_page_row_buttons( $actions, $page_object ) {
	// don't add url to blacklisted content types
	if ( ! tve_is_post_type_editable( $page_object->post_type ) || ! TCB_Product::has_post_access( $page_object->ID ) ) {
		return $actions;
	}

	$page_for_posts = get_option( 'page_for_posts' );
	if ( $page_for_posts && $page_object->ID == $page_for_posts ) {
		return $actions;
	}

	?>
	<style type="text/css">
        .thrive-adminbar-icon {
            background: url('<?php echo tve_editor_css(); ?>/images/admin-bar-logo.png') no-repeat 0 0;
            background-size: contain;
            padding-left: 25px;
        }
	</style>
	<?php

	$url            = tcb_get_editor_url( $page_object->ID );
	$actions['tcb'] = '<span class="thrive-adminbar-icon"></span><a target="_blank" href="' . $url . '">' . __( 'Edit with Thrive Architect', 'thrive-cb' ) . '</a>';

	return $actions;
}

/**
 * Load meta tags for social media and others
 *
 * @param int $post_id
 */
function tve_load_meta_tags( $post_id = 0 ) {

	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}
	$globals = tve_get_post_meta( $post_id, 'tve_globals' );
	if ( ! empty( $globals['fb_comment_admins'] ) ) {
		$fb_admins = json_decode( $globals['fb_comment_admins'] );
		if ( ! empty( $fb_admins ) && is_array( $fb_admins ) ) {
			foreach ( $fb_admins as $admin ) {
				echo '<meta property="fb:admins" content="' . $admin . '"/>';
			}
		}
	}
}

/**
 * Returns global style for an element given as a parameter
 *
 * @param string $for_element
 * @param string $option_name
 * @param int    $for_post
 *
 * @return array
 */
function tve_get_global_styles( $for_element = '', $option_name = '', $for_post = 0 ) {
	if ( empty( $option_name ) ) {
		$global_style_options = tve_get_global_styles_option_names();
		$option_name          = $global_style_options[ $for_element ];
	}

	if ( ! empty( $for_post ) ) {
		$global_styles = get_post_meta( $for_post, $option_name, true );
	} else {
		$global_styles = get_option( $option_name, array() );
	}

	$global_styles = apply_filters( 'tcb_global_styles', $global_styles );

	$element_global_styles = array();

	if ( ! is_array( $global_styles ) ) {
		/**
		 * Avoid cases where the user is modifying the DB
		 */
		$global_styles = array();
	}

	foreach ( $global_styles as $identifier => $styles ) {

		$element_global_styles[] = array(
			'id'           => $identifier,
			'name'         => stripslashes( $styles['name'] ),
			'cls'          => constant( 'TVE_GLOBAL_STYLE_' . strtoupper( $for_element ) . '_CLS_PREFIX' ) . $identifier,
			'attr'         => empty( $styles['dom']['attr'] ) ? array() : $styles['dom']['attr'],
			'default_css'  => empty( $styles['default_css'] ) ? array() : $styles['default_css'],
			'default_html' => empty( $styles['default_html'] ) ? array() : $styles['default_html'],
			'smart_config' => empty( $styles['smart_config'] ) ? array() : $styles['smart_config'],
		);

	}

	return $element_global_styles;
}

/**
 * Hook on wp_head WP Action
 *
 * Outputs Thrive Global Variables
 */
function tve_load_global_variables() {
	$global_colors    = get_option( apply_filters( 'tcb_global_colors_option_name', 'thrv_global_colours' ), array() );
	$global_gradients = get_option( apply_filters( 'tcb_global_gradients_option_name', 'thrv_global_gradients' ), array() );

	echo '<style type="text/css" id="tve_global_variables">';
	echo ':root{';
	foreach ( $global_colors as $color ) {
		echo TVE_GLOBAL_COLOR_VAR_CSS_PREFIX . $color['id'] . ':' . $color['color'] . ';';
	}
	foreach ( $global_gradients as $gradient ) {
		echo TVE_GLOBAL_GRADIENT_VAR_CSS_PREFIX . $gradient['id'] . ':' . $gradient['gradient'] . ';';
	}

	/**
	 * Insert extra global variables in the tve_global_variables style node
	 */
	do_action( 'tcb_get_extra_global_variables' );

	echo '}';
	echo '</style>';
}

/**
 * Outputs the global styles inside the main frame
 */
function tve_load_global_styles() {
	echo tve_get_shared_styles( '', '300' );
}

/**
 * Prepares the outputted CSS string by replacing the CSS Variables with their values
 *
 * @param string $css_string
 * @param bool   $bypass_editor_check
 * @param bool   $allow_lp_vars
 *
 * @return mixed|string
 */
function tve_prepare_global_variables_for_front( $css_string = '', $bypass_editor_check = false, $allow_lp_vars = true ) {
	if ( false === $bypass_editor_check && is_editor_page_raw() ) {
		return tcb_custom_css( $css_string );
	}

	$global_colors    = get_option( apply_filters( 'tcb_global_colors_option_name', 'thrv_global_colours' ), array() );
	$global_gradients = get_option( apply_filters( 'tcb_global_gradients_option_name', 'thrv_global_gradients' ), array() );


	$search  = array();
	$replace = array();

	foreach ( $global_colors as $color ) {
		$search[]  = 'var(' . TVE_GLOBAL_COLOR_VAR_CSS_PREFIX . $color['id'] . ')';
		$replace[] = $color['color'];
	}

	foreach ( $global_gradients as $gradient ) {
		$search[]  = 'var(' . TVE_GLOBAL_GRADIENT_VAR_CSS_PREFIX . $gradient['id'] . ')';
		$replace[] = $gradient['gradient'];
	}


	if ( $allow_lp_vars && wp_doing_ajax() && ! empty( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) ) {
		/**
		 * For AJAX Requests we need also that filter to be called
		 *
		 * Therefore we instantiate a landing page object if the provided post is a landing page
		 */
		$post = tcb_post( $_REQUEST['post_id'] );
		if ( $post->is_landing_page() ) {
			tcb_landing_page( $_REQUEST['post_id'] );
		}
	}

	$front_variables = apply_filters( 'tcb_prepare_global_variables_for_front', $search, $replace );
	if ( ! empty( $front_variables['search'] ) && ! empty( $front_variables['replace'] ) ) {
		$search  = array_merge( $search, $front_variables['search'] );
		$replace = array_merge( $replace, $front_variables['replace'] );
	}

	$css_string = str_replace( $search, $replace, $css_string );

	return tcb_custom_css( $css_string );
}

/**
 * Prepares the master variables for output
 *
 * Used in the ThriveTheme and in TAR
 *
 * @param array $master_variable
 *
 * @return string
 */
function tve_prepare_master_variable( $master_variable = array() ) {

	if ( empty( $master_variable['hsl'] ) || ! is_array( $master_variable['hsl'] ) ) {
		return '';
	}

	$master_config = array(
		TVE_MAIN_COLOR_H . ':' . $master_variable['hsl']['h'],
		TVE_MAIN_COLOR_S . ':' . ( (float) $master_variable['hsl']['s'] * 100 ) . '%',
		TVE_MAIN_COLOR_L . ':' . ( (float) $master_variable['hsl']['l'] * 100 ) . '%',
	);

	return implode( ';', $master_config ) . ';';
}


/**
 * it's a hook on the wp_head WP action
 *
 * outputs the CSS needed for the custom fonts
 */
function tve_load_font_css() {
	do_action( 'tcb_extra_fonts_css' );

	$all_fonts = tve_get_all_custom_fonts();
	if ( empty( $all_fonts ) ) {
		return;
	}
	echo '<style type="text/css">';

	/** @var array $css prepare and array of css classes what will have as value an array of css rules */
	$css = array();
	foreach ( $all_fonts as $font ) {
		$css[ $font->font_class ] = array(
			'font-family: ' . tve_prepare_font_family( $font->font_name ) . ' !important;',
		);
		$font_weight              = preg_replace( '/[^0-9]/', '', $font->font_style );
		$font_style               = preg_replace( '/[0-9]/', '', $font->font_style );
		if ( ! empty( $font->font_color ) ) {
			$css[ $font->font_class ][] = "color: {$font->font_color};";
		}
		if ( ! empty( $font_weight ) ) {
			$css[ $font->font_class ][] = "font-weight: {$font_weight} !important;";
		}
		if ( ! empty( $font_style ) ) {
			$css[ $font->font_class ][] = "font-style: {$font_style};";
		}
		if ( ! empty( $font->font_bold ) ) {
			$arr_key         = "{$font->font_class}.bold_text,.{$font->font_class} .bold_text,.{$font->font_class} b,.{$font->font_class} strong";
			$css[ $arr_key ] = array(
				"font-weight: {$font->font_bold} !important;",
			);
		}
	}

	/**
	 * Loop through font classes and display their css properties
	 *
	 * @var string $font_class
	 * @var array  $rules
	 */
	foreach ( $css as $font_class => $rules ) {
		/** add font css rules to the page */
		echo tcb_selection_root() . " .{$font_class}{" . implode( '', $rules ) . '}';
		/** set the font css rules for inputs also */
		echo ".{$font_class} input, .{$font_class} select, .{$font_class} textarea, .{$font_class} button {" . implode( '', $rules ) . '}';
	}

	echo '</style>';

}

/**
 * output the css for the $fonts array
 *
 * @param array $fonts
 */
function tve_output_custom_font_css( $fonts ) {
	echo '<style type="text/css">';

	/** @var array $css prepare and array of css classes what will have as value an array of css rules */
	$css = array();
	foreach ( $fonts as $font ) {
		$font                     = (object) $font;
		$css[ $font->font_class ] = array(
			'font-family: ' . ( strpos( $font->font_name, ',' ) === false ? "'" . $font->font_name . "'" : $font->font_name ) . ' !important;',
		);

		$font_weight = preg_replace( '/[^0-9]/', '', $font->font_style );
		$font_style  = preg_replace( '/[0-9]/', '', $font->font_style );
		if ( ! empty( $font->font_color ) ) {
			$css[ $font->font_class ][] = "color: {$font->font_color} !important;";
		}
		if ( ! empty( $font_weight ) ) {
			$css[ $font->font_class ][] = "font-weight: {$font_weight} !important;";
		}
		if ( ! empty( $font_style ) ) {
			$css[ $font->font_class ][] = "font-style: {$font_style};";
		}
		if ( ! empty( $font->font_bold ) ) {
			$font_key         = "{$font->font_class}.bold_text,.{$font->font_class} .bold_text,.{$font->font_class} b,.{$font->font_class} strong";
			$css[ $font_key ] = array(
				"font-weight: {$font->font_bold} !important;",
			);
		}
	}

	/**
	 * Loop through font classes and display their css properties
	 *
	 * @var string $font_class
	 * @var array  $rules
	 */
	foreach ( $css as $font_class => $rules ) {
		/** add font css rules to the page */
		echo ".{$font_class}{" . implode( '', $rules ) . '}';
		/** set the font css rules for inputs also */
		echo ".{$font_class} input, .{$font_class} select, .{$font_class} textarea, .{$font_class} button {" . implode( '', $rules ) . '}';
	}

	echo '</style>';
}

/**
 * Prepare font family name to be added to css rule
 *
 * @param $font_family
 */
function tve_prepare_font_family( $font_family ) {
	$chunks = explode( ',', $font_family );
	$length = count( $chunks );
	$font   = '';
	foreach ( $chunks as $key => $value ) {
		$font .= "'" . trim( $value ) . "'";
		$font .= ( $key + 1 ) < $length ? ', ' : '';
	}

	return $font;
}

/**
 * Adds an icon and link to the admin bar for quick access to the editor. Only shows when not already in Thrive Architect
 *
 * @param array  $nodes
 * @param string $thrive_node_id
 *
 * @return array|void
 */
function thrive_editor_admin_bar( $nodes ) {
	$theme = wp_get_theme();
	// SUPP-1408 Hive theme leaves the query object in an unknown state
	if ( 'Hive' === $theme->name || 'Hive' === $theme->parent_theme ) {
		wp_reset_query();
	}
	$post_id = get_the_ID();
	if ( is_admin_bar_showing() && ( is_single() || is_page() ) && tve_is_post_type_editable( get_post_type() ) && TCB_Product::has_external_access( $post_id ) ) {

		if ( ! isset( $_GET[ TVE_EDITOR_FLAG ] ) ) {
			$editor_link = tcb_get_editor_url( $post_id );
			$args        = array(
				'id'    => 'tve_button',
				'title' => '<span class="thrive-adminbar-tar-icon"></span>' . __( 'Edit with Thrive Architect', 'thrive-cb' ),
				'href'  => $editor_link,
				'meta'  => array(
					'class' => 'thrive-admin-tar',
				),
			);
		} elseif ( get_post_type() === 'post' || get_post_type() === 'page' ) {
			$close_editor_link = tcb_get_editor_close_url( $post_id );
			$args              = array(
				'id'    => 'tve_button',
				'title' => '<span class="thrive-adminbar-icon"></span>' . __( 'Close Thrive Architect', 'thrive-cb' ),
				'href'  => $close_editor_link,
				'meta'  => array(
					'class' => 'thrive-admin-bar',
				),
			);
		} else {
			return;
		}

		$args['order'] = 0;
		$nodes[]       = $args;
	}

	return $nodes;
}

/**
 * Checks for [embed] shortcodes inside the content and uses the run_shortcode() function from class-wp-embed.php to render them instead of using do_shortcode() .
 *
 * @param $content
 *
 * @return mixed
 */
function tve_handle_embed_shortcode( $content ) {
	/* if we find an [embed] tag, give the content to the run_shortcode() function from class-wp-embed */
	if ( strpos( $content, '[embed' ) !== false ) {
		global $wp_embed;
		$content = $wp_embed->run_shortcode( $content );
	}

	return $content;
}

/**
 * add the editor content to $content, but at priority 101 so not affected by custom theme shortcode functions that are common with some theme developers
 *
 * @param string      $content  the post content
 * @param null|string $use_case used to control the output, e.g. it can be used to return just TCB content, not full content
 *
 * @return string
 */
function tve_editor_content( $content, $use_case = null ) {
	global $post;

	$tcb_post = tcb_post( $post );

	$is_editor_page = is_editor_page();

	$post_id = get_the_ID();

	if ( isset( $GLOBALS['TVE_CONTENT_SKIP_ONCE'] ) ) {
		unset( $GLOBALS['TVE_CONTENT_SKIP_ONCE'] );

		return $content;
	}

	/**
	 * check if current post is protected by a membership plugin
	 */
	if ( ! tve_membership_plugin_can_display_content() ) {
		return $content;
	}

	if ( ! tve_is_post_type_editable( get_post_type( $post_id ) ) ) {
		return $content;
	}

	$is_landing_page   = tve_post_is_landing_page( $post_id );
	$tcb_force_excerpt = false;

	if ( $use_case !== 'tcb_content' && post_password_required( $post ) ) {
		return $is_landing_page ? '<div class="tve-lp-pw-form">' . get_the_password_form( $post ) . '</div>' : $content;
	}

	if ( $is_editor_page ) {
		// this is an editor page
		$tve_saved_content = tve_get_post_meta( $post_id, 'tve_updated_post', true );

		/**
		 * SUPP-4806 Conflict (max call stack exceeded most likely) with Yoast SEO Address / Map Widgets
		 */
		if ( doing_filter( 'get_the_excerpt' ) || doing_filter( 'the_excerpt' ) ) {
			return $tve_saved_content . $content;
		}

		/**
		 * If there is no TCB-saved content, but the post / page contains WP content, create a WP-Content element in TCB containing everything from WP
		 */
		if ( empty( $tve_saved_content ) ) {
			$tve_saved_content = $tcb_post->get_wp_element();
			$tcb_post->meta( 'tcb2_ready', 1 );
		}
	} else {
		/* SUPP-2680 - removed the custom css display from here - it's loaded from the wp_enqueue_scripts hook */

		if ( $use_case !== 'tcb_content' ) { // do not trucate the contents if we require it all
			/* if the editor was specifically disabled for this post, just return the content */
			if ( $tcb_post->editor_disabled() ) {
				return $content;
			}
			/**
			 * do not truncate the post content if the current page is a feed and the option for the feed display is "Full text"
			 */
			$rss_use_excerpt = false;
			if ( is_feed() ) {
				$rss_use_excerpt = (bool) get_option( 'rss_use_excerpt' );
			}
			$tcb_force_excerpt = apply_filters( 'tcb_force_excerpt', false );
			if ( $rss_use_excerpt || ! is_singular() || $tcb_force_excerpt ) {
				$more_found          = tve_get_post_meta( get_the_ID(), 'tve_content_more_found', true );
				$content_before_more = tve_get_post_meta( get_the_ID(), 'tve_content_before_more', true );
				if ( $more_found ) {
					if ( is_feed() ) {
						$more_link = ' [&#8230;]';
					} else {
						$more_link = apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . '#more-' . $post->ID . '" class="more-link">' . __( 'Continue Reading', 'thrive-cb' ) . '</a>', __( 'Continue Reading', 'thrive-cb' ) );
					}

					$tve_saved_content = $content_before_more . $more_link;
					$tve_saved_content = force_balance_tags( $tve_saved_content );
					$content           = ''; /* clear out anything else after this point */
					$content_trimmed   = true;
				} elseif ( is_feed() && $rss_use_excerpt ) {
					$rss_content = tve_get_post_meta( $post_id, 'tve_updated_post', true ) . $content;
					if ( $rss_content ) {
						$tve_saved_content = wp_trim_excerpt( $rss_content );
					}
					$content_trimmed = true;
				}
			}
		}

		if ( ! isset( $tve_saved_content ) ) {
			$tve_saved_content = tve_get_post_meta( $post_id, 'tve_updated_post', true );
			$tve_saved_content = tve_restore_script_tags( $tve_saved_content );
		}
		if ( empty( $tve_saved_content ) ) {
			// return empty content if nothing is inserted in the editor - this is to make sure that first page section on the page will actually be displayed ok
			return $use_case === 'tcb_content' ? '' : $content;
		}

		$tve_saved_content = tve_compat_content_filters_before_shortcode( $tve_saved_content );

		/**
		 * Prepare Events configuration
		 * We only skip feeds page here. The content can be placed anywhere on the page
		 * We have to cover the case when the page is not a landing page and the content is outside the loop
		 */
		if ( ! is_feed() ) {
			// append lightbox HTML to the end of the body
			tve_parse_events( $tve_saved_content );
		}

		/* make images responsive */
		if ( function_exists( 'wp_filter_content_tags' ) ) {
			$tve_saved_content = wp_filter_content_tags( $tve_saved_content );
		} elseif ( function_exists( 'wp_make_content_images_responsive' ) ) {
			$tve_saved_content = wp_make_content_images_responsive( $tve_saved_content );
		}
	}

	$tve_saved_content = tve_thrive_shortcodes( $tve_saved_content, $is_editor_page );

	/* render the content added through WP Editor (element: "WordPress Content") */
	$tve_saved_content = tve_do_wp_shortcodes( $tve_saved_content, $is_editor_page );

	if ( ! $is_editor_page ) {
		//for the case when user put a shortcode inside a "p" element
		$tve_saved_content = shortcode_unautop( $tve_saved_content );

		/* search for WP's <!--more--> tag and split the content based on that */
		if ( $use_case !== 'tcb_content' && ( ! is_singular() || $tcb_force_excerpt ) && ! isset( $content_trimmed ) ) {
			if ( preg_match( '#<!--more(.*?)?-->#', $tve_saved_content, $m ) ) {
				list( $tve_saved_content ) = explode( $m[0], $tve_saved_content, 2 );

				$tve_saved_content = preg_replace( '#<p>$#s', '', $tve_saved_content );
				$more_link         = apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . '#more-' . $post->ID . '" class="more-link">' . __( 'Continue Reading', 'thrive-cb' ) . '</a>', __( 'Continue Reading', 'thrive-cb' ) );
				$tve_saved_content = force_balance_tags( $tve_saved_content . $more_link );
			}
		}

		/* fix for SUPP-5168, treat [embed] shortcodes separately by delegating the shortcode function to class-wp-embed.php */
		$tve_saved_content = tve_handle_embed_shortcode( $tve_saved_content );

		if ( $is_landing_page ) {
			$tve_saved_content = do_shortcode( $tve_saved_content );
			$tve_saved_content = tve_compat_content_filters_after_shortcode( $tve_saved_content );
		} else {
			$theme = wp_get_theme();
			/**
			 * Stendhal theme removes the default WP do_shortcode on the_content filter and adds their own. not sure why
			 */
			if ( $theme->name === 'Stendhal' || $theme->parent_theme === 'Stendhal' ) {
				$tve_saved_content = do_shortcode( $tve_saved_content );
			}
		}
	}
	/**
	 * Replace again {tcb_} shortcodes in case they are used in shortcodes
	 */
	$tve_saved_content = tve_do_custom_content_shortcodes( $tve_saved_content );

	$style_family_class = tve_get_style_family_class( $post_id );

	$style_family_id = is_singular() ? ' id="' . $style_family_class . '" ' : ' ';

	$wrap = array(
		'start' => '<div' . $style_family_id . 'class="' . $style_family_class . ' tcb-style-wrap"><div id="tve_editor" class="tve_shortcode_editor tar-main-content" data-post-id="' . $post_id . '">',
		'end'   => '</div></div>',
	);

	/* don't wrap when page is feed OR when we're rendering a post list inside the editor
	 * use case that breaks - add post list in top section with full content => when editing the post, tve_editor will be the one from the post list, not the one from the current post
	*/
	if ( is_feed() || ( ! TCB_Post_List::is_outside_post_list_render() && is_editor_page_raw( true ) ) ) {
		$wrap['start'] = $wrap['end'] = '';
	} elseif ( $is_editor_page && get_post_type( $post_id ) === 'tcb_lightbox' ) {
		$wrap['start'] .= '<div class="tve_p_lb_control tve_editor_main_content tve_content_save tve_empty_dropzone">';
		$wrap['end']   .= '</div>';
	}

	if ( tve_get_post_meta( $post_id, 'thrive_icon_pack' ) ) {
		TCB_Icon_Manager::enqueue_icon_pack();
	}

	tve_enqueue_extra_resources( $post_id );

	/**
	 * fix for LG errors being included in the page
	 */
	$tve_saved_content = preg_replace_callback( '/__CONFIG_lead_generation__(.+?)__CONFIG_lead_generation__/s', 'tcb_lg_err_inputs', $tve_saved_content );

	$tve_saved_content = apply_filters( $is_editor_page ? 'tcb_alter_editor_content' : 'tcb_clean_frontend_content', $tve_saved_content );

	$tve_saved_content = tcb_remove_deprecated_strings( $tve_saved_content );

	if ( $use_case === 'tcb_content' ) {
		return $tve_saved_content;
	}

	if ( doing_filter( 'get_the_excerpt' ) ) {
		/* add some space for when the content is stripped for the excerpt */
		$tve_saved_content = str_replace( '</p><p>', '</p>&nbsp;<p>', $tve_saved_content );
	}

	/**
	 * Change post / page content
	 *
	 * @param string $tve_saved_content
	 * @param bool   $is_landing_page
	 */
	$tve_saved_content = apply_filters( 'tcb.landing_page_content', $tve_saved_content, $is_landing_page );

	if ( $is_landing_page ) {

		$header = TCB_Symbol_Template::symbol_render_shortcode( array(
			'id'                   => get_post_meta( $post_id, '_tve_header', true ),
			'tve_shortcode_config' => $is_editor_page,
		), true );
		$footer = TCB_Symbol_Template::symbol_render_shortcode( array(
			'id'                   => get_post_meta( $post_id, '_tve_footer', true ),
			'tve_shortcode_config' => $is_editor_page,
		), true );

		$tve_saved_content = $header . $tve_saved_content . $footer;
	}

	return $wrap['start'] . $tve_saved_content . $wrap['end'] . $content;
}

/**
 * Pre-process of content before serving it - remove some of the problem strings reported by customers
 * Ensure backward-compatibility with fixed issues - e.g. remove "noopener" and "noreferrer" attributes
 *
 * @param string $content
 *
 * @return string
 */
function tcb_remove_deprecated_strings( $content ) {
	$content = str_replace( array(
		' data-default="Your Heading Here"',
		' data-default="Enter your text here..."',
	), array( '', '' ), $content );
	$content = str_replace( array( ' rel="noopener noreferrer"', ' rel="noreferrer noopener"' ), '', $content );
	$content = str_replace( array(
		' rel="nofollow noopener noreferrer"',
		' rel="noreferrer noopener nofollow"',
	), ' rel="nofollow"', $content );
	$content = str_replace( array(
		' rel="noopener nofollow noreferrer"',
		' rel="noreferrer nofollow noopener"',
	), ' rel="nofollow"', $content );

	/**
	 * Action filter - remove deprecated texts
	 */
	return apply_filters( 'tcb_remove_deprecated_strings', $content );
}

/**
 * Updating the Theme first and then trying to activate TAr results in a fatal error
 */
if ( ! function_exists( 'tve_save_post_callback' ) ) {
	/**
	 * When a page is edited from admin -> we need to use the same title for the associated lightbox, if the page in question is a landing page
	 * Copy post tve meta to revision meta
	 *
	 * This method is also called when a revision of a post is added
	 *
	 * @param $post_id
	 *
	 * @see defaults-filters.php for add_action("post_updated")
	 *
	 * @see wp_insert_post which is doing: "post_updated", "save_post"
	 */
	function tve_save_post_callback( $post_id ) {
		/**
		 * If $post_id is an ID of a revision POST
		 */
		if ( $parent_id = wp_is_post_revision( $post_id ) ) {

			$meta_keys = tve_get_used_meta_keys();

			/**
			 * copy post metas to its revision
			 */
			foreach ( $meta_keys as $meta_key ) {
				if ( $meta_key === 'tve_landing_page' ) {
					$meta_value = get_post_meta( $parent_id, $meta_key, true );
				} else {
					$meta_value = tve_get_post_meta( $parent_id, $meta_key );
				}
				add_metadata( 'post', $post_id, 'tve_revision_' . $meta_key, $meta_value );
			}
		}

		$post_type = get_post_type( $post_id );
		if ( $post_type !== 'page' ) {
			return;
		}
		$is_landing_page = tve_post_is_landing_page( $post_id );
		$tve_globals     = tve_get_post_meta( $post_id, 'tve_globals' );

		if ( ! $is_landing_page || empty( $tve_globals['lightbox_id'] ) ) {
			return;
		}

		$lightbox = get_post( $tve_globals['lightbox_id'] );

		if ( ! $lightbox || ! ( $lightbox instanceof WP_Post ) || $lightbox->post_type !== 'tcb_lightbox' ) {
			return;
		}

		wp_update_post( array(
			'ID'         => $tve_globals['lightbox_id'],
			'post_title' => 'Lightbox - ' . get_the_title( $post_id ),
		) );
	}
}

/**
 * Filter the wp content out of the post for posts that only use TCB content
 *
 * @param string $content
 *
 * @return string
 */
function tve_clean_wp_editor_content( $content ) {
	if ( post_password_required() || ! tve_is_post_type_editable( get_post_type() ) ) {
		return $content;
	}

	if ( ! tve_membership_plugin_can_display_content() ) {
		return $content;
	}

	$tcb_post = tcb_post();

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	/**
	 * Optimize Press Conflict With TAR
	 * If the page is an optimize press page, we return the page
	 */
	$is_optimize_press_page = get_post_meta( $tcb_post->ID, '_optimizepress_pagebuilder', true );
	if ( ! empty( $is_optimize_press_page ) && is_plugin_active( 'optimizePressPlugin/optimizepress.php' ) ) {
		return $content;
	}


	/**
	 * WPBakery Visual Composer Conflict with TAR
	 * https://wpbakery.com/
	 * If the page is an WPBakery Visual Composer page, we return the page
	 */
	$is_visual_composer = get_post_meta( $tcb_post->ID, '_wpb_vc_js_status', true );
	if ( ! empty( $is_visual_composer ) && is_plugin_active( 'js_composer/js_composer.php' ) && ! $tcb_post->meta( 'tcb_editor_enabled' ) ) {
		return $content;
	}


	if ( $tcb_post->meta( 'tcb_editor_enabled' ) ) {
		$content = '<div class="tcb_flag" style="display: none"></div>';
	} elseif ( is_editor_page() ) {
		/**
		 * Introduced content checks to avoid saving this meta key for posts not being edited with TAr
		 */
		$has_tcb_content = $tcb_post->meta( 'tve_globals', null, true );
		if ( $tcb_post->meta( 'tcb2_ready' ) || empty( $tcb_post->post_content ) || ! $has_tcb_content ) {
			$content = '<div class="tcb_flag" style="display: none"></div>';
		}
	}

	return $content;
}

/**
 * check if there are any extra icon packs needed on the current page / post
 *
 * @param $post_id
 */
function tve_enqueue_extra_resources( $post_id ) {
	$globals = tve_get_post_meta( $post_id, 'tve_globals' );

	if ( ! empty( $globals['used_icon_packs'] ) && ! empty( $globals['extra_icons'] ) ) {
		$used_icons_font_family = $globals['used_icon_packs'];

		foreach ( $globals['extra_icons'] as $icon_pack ) {
			if ( ! in_array( $icon_pack['font-family'], $used_icons_font_family ) ) {
				continue;
			}
			wp_enqueue_style( md5( $icon_pack['css'] ), tve_url_no_protocol( $icon_pack['css'] ) );
		}
	}

	/* any of the extra imported fonts - only in case of imported landing pages */
	if ( ! empty( $globals['extra_fonts'] ) ) {
		foreach ( $globals['extra_fonts'] as $font ) {
			if ( empty( $font['ignore'] ) ) {
				wp_enqueue_style( md5( $font['font_url'] ), tve_url_no_protocol( $font['font_url'] ) );
			}
		}
	}
}

/**
 * wrapper over the wp enqueue_style function
 * it will append the TVE_VERSION as a query string parameter to the $src if $ver is left empty
 *
 * @param       $handle
 * @param       $src
 * @param array $deps
 * @param bool  $ver
 * @param       $media
 */
function tve_enqueue_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
	if ( $ver === false ) {
		$ver = TVE_VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * wrapper over the wp_enqueue_script functions
 * it will add the plugin version to the script source if no version is specified
 *
 * @param        $handle
 * @param string $src
 * @param array  $deps
 * @param bool   $ver
 * @param bool   $in_footer
 */
function tve_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
	if ( $ver === false ) {
		$ver = TVE_VERSION;
	}
	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * some features in the editor can only be displayed if we have knowledge about the theme and thus should only display on a thrive theme (borderless content for instance)
 * this function checks the global variable that's set in all thrive themes to check if the user is using a thrive theme or not
 **/
function tve_check_if_thrive_theme() {
	global $is_thrive_theme;
	if ( isset( $is_thrive_theme ) && $is_thrive_theme == true ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Whitelist filter for custom fields that are considered protected by other plugins but want to be displayed
 *
 * @param $meta_key
 *
 * @return bool
 */
function tve_whitelist_custom_fields( $meta_key ) {
	return in_array( $meta_key, TCB_Custom_Fields_Shortcode::$whitelisted_fields );
}

/**
 * Hides thrive editor custom fields from being modified in the standard WP post / page edit screen
 *
 * @param $protected
 * @param $meta_key
 *
 * @return bool
 */
function tve_hide_custom_fields( $protected, $meta_key ) {

	if ( tve_whitelist_custom_fields( $meta_key ) ) {
		return false;
	}

	foreach ( TCB_Custom_Fields_Shortcode::$protected_fields as $key ) {
		if ( $key == $meta_key || strpos( $meta_key, $key ) === 0 || ! ! get_post_meta( get_the_ID(), '_' . $meta_key, true ) ) {
			return true;
		}
	}

	return $protected;
}

/**
 * This is a replica of the WP function get_extended
 * The returned array has 'main', 'extended', and 'more_text' keys. Main has the text before
 * the <code><!--tvemore--></code>. The 'extended' key has the content after the
 * <code><!--tvemore--></code> comment. The 'more_text' key has the custom "Read More" text.
 *
 * @param string $post Post content.
 *
 * @return array Post before ('main'), after ('extended'), and custom readmore ('more_text').
 */
function tve_get_extended( $post ) {

	//Match the "More..." nodes
	$more_tag = '#<!--tvemorestart-->(.+?)<!--tvemoreend-->#s';

	if ( preg_match( $more_tag, $post, $matches ) ) {
		list( $main, $extended ) = explode( $matches[0], $post, 2 );
		$more_text  = $matches[1];
		$more_found = true;
	} else {
		$main       = $post;
		$extended   = '';
		$more_text  = '';
		$more_found = false;
	}

	// ` leading and trailing whitespace
	$main      = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $main );
	$extended  = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $extended );
	$more_text = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $more_text );

	return array(
		'main'       => $main,
		'extended'   => $extended,
		'more_text'  => $more_text,
		'more_found' => $more_found,
	);
}

/**
 * Adds inline script to hide more tag from the front end display
 *
 * @depricated
 */
function tve_hide_more_tag() {
	_doing_it_wrong(
		__FUNCTION__,
		'This is deprecated since TAr version 2.4.5',
		'2.4.5'
	);
}

/**
 * if the current post is a landing page created with TCB, forward the control over to the landing page layout.php file
 *
 * if the current post is a Thrive CB Lightbox, display it on a page that will mimic it's behaviour (semi-transparent background, close button etc)
 *
 * if there is a hook registered for displaying content, call that hook
 *
 * @return bool
 */
function tcb_custom_editable_content() {
	// don't apply template redirects unless single post / page is being displayed.
	if ( ! apply_filters( 'tcb_is_editor_page', is_singular() ) || is_feed() || is_comment_feed() ) {
		return false;
	}

	$allow_landing_page_edit = apply_filters( 'tcb_allow_landing_page_edit', tve_in_architect() );

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	/**
	 * the filter should append its own custom templates based on the post ID / type
	 * if this array is not empty, it will use the first found file from this array as the post content template
	 */
	$custom_post_layouts = apply_filters( 'tcb_custom_post_layouts', array(), $post_id, $post_type );

	/* For TCB, we only have tcb_lightbox and landing pages editable with a separate layout */
	if ( $post_type !== 'tcb_lightbox' && ! ( $lp_template = tve_post_is_landing_page( $post_id ) ) && empty( $custom_post_layouts ) ) {
		return false;
	}

	$landing_page_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'landing-page';

	if ( $allow_landing_page_edit && $post_type === 'tcb_lightbox' ) {
		tcb_lightbox( $post_id )->output_layout();
		exit();
	}

	if ( $allow_landing_page_edit && ! empty( $lp_template ) ) {
		/**
		 * first, check if a membership plugin is protecting this page and, if the user does not have access, just proceed with the regular page content
		 */
		if ( ! tve_membership_plugin_can_display_content() ) {
			return false;
		}

		/* instantiate the $tcb_landing_page object - this is used throughout the layout.php for the landing page */
		$tcb_landing_page = tcb_landing_page( $post_id, $lp_template );

		$GLOBALS['tcb_lp_template']  = $lp_template;
		$GLOBALS['tcb_landing_page'] = $tcb_landing_page;

		/* base CSS file for all Page Templates */
		if ( ! tve_check_if_thrive_theme() ) {
			tve_enqueue_style( 'tve_landing_page_base_css', TVE_LANDING_PAGE_TEMPLATE . '/css/base.css', 99 );
		}

		$tcb_landing_page->enqueue_css();
		$tcb_landing_page->ensure_external_assets();

		include_once ABSPATH . '/wp-admin/includes/plugin.php';
		if ( is_editor_page() || ! tve_hooked_in_template_redirect() ) {

			/**
			 * added this here, because setting up a Landing Page as the homepage of your site would cause WP to not redirect properly non-www homepage to www homepage
			 */
			redirect_canonical();

			/**
			 * Allow hooking before any output is generated for a landing page
			 *
			 * @param string           $template_path    full path to the template file being rendered
			 * @param TCB_Landing_Page $tcb_landing_page landing page instance
			 */
			do_action( 'tcb_landing_page_template_redirect', $landing_page_dir . '/layout.php', $tcb_landing_page );

			/* give the control over to the landing page template */
			include $landing_page_dir . '/layout.php';
			exit();
		}

		/**
		 * Mark the fact that we removed the 'the_content' filter so we can add it back ( in landing-page/layout.php ).
		 */
		$GLOBALS['tcb_landing_page_needs_filter'] = true;
		/**
		 * temporarily remove the_content filter for landing pages (just to not output anything in the head) - it caused issues on some shortcodes.
		 * this is re-added from the landing page layout.php file
		 */
		remove_filter( 'the_content', 'tve_editor_content' );
		/**
		 * remove thrive_template_redirect filter from the themes
		 */
		remove_filter( 'template_redirect', 'thrive_template_redirect' );

		/**
		 * this is a fix for conflicts appearing with various membership / coming soon plugins that use the template_redirect hook
		 */
		remove_all_filters( 'template_include' );
		add_filter( 'template_include', 'tcb_get_landing_page_template_layout' );

		/**
		 * Add template_include Filter After they were removed
		 */
		tve_compat_re_add_template_include_filters();

		/**
		 * make sure we'll have at least one of these fired
		 */
		add_filter( 'page_template', 'tcb_get_landing_page_template_layout' );

	} elseif ( $post_type != 'post' && $post_type != 'page' && ! empty( $custom_post_layouts ) && is_array( $custom_post_layouts ) ) {
		/**
		 * loop through each of the post_custom_layouts files array to find the first valid one
		 */
		foreach ( $custom_post_layouts as $file ) {
			$file = @realpath( $file );
			if ( ! is_file( $file ) ) {
				continue;
			}
			include $file;
			exit();
		}
	}
}

/**
 * @param string $template
 *
 * @return string the full path to the landing page layout template
 */
function tcb_get_landing_page_template_layout( $template ) {
	return plugin_dir_path( dirname( __FILE__ ) ) . 'landing-page/layout.php';
}

/**
 * parse and prepare all the required configuration for the different events
 *
 * @param string $content TCB - meta post content
 */
function tve_parse_events( &$content ) {
	list( $start, $end ) = array(
		'__TCB_EVENT_',
		'_TNEVE_BCT__',
	);
	if ( strpos( $content, $start ) === false ) {
		return;
	}
	$triggers = tve_get_event_triggers();
	$actions  = tve_get_event_actions();

	$event_pattern = "#data-tcb-events=('|\"){$start}(.+?){$end}('|\")#";

	/* hold all the javascript callbacks required for the identified actions */
	$javascript_callbacks = isset( $GLOBALS['tve_event_manager_callbacks'] ) ? $GLOBALS['tve_event_manager_callbacks'] : array();
	/* holds all the Global JS required by different actions and event triggers on page load */
	$registered_javascript_globals = isset( $GLOBALS['tve_event_manager_global_js'] ) ? $GLOBALS['tve_event_manager_global_js'] : array();

	/* hold all instances of the Action classes in order to output stuff in the footer, we need to get out of the_content filter */
	$registered_actions = isset( $GLOBALS['tve_event_manager_actions'] ) ? $GLOBALS['tve_event_manager_actions'] : array();

	/*
     * match all instances for Event Configurations
     */
	if ( preg_match_all( $event_pattern, $content, $matches, PREG_OFFSET_CAPTURE ) !== false ) {

		foreach ( $matches[2] as $i => $data ) {
			$m = htmlspecialchars_decode( $data[0] ); // the actual matched regexp group
			if ( ! ( $_params = json_decode( $m, true ) ) ) {
				$_params = array();
			}
			if ( empty( $_params ) ) {
				continue;
			}

			foreach ( $_params as $index => $event_config ) {
				if ( empty( $event_config['t'] ) || empty( $event_config['a'] ) || ! isset( $triggers[ $event_config['t'] ] ) || ! isset( $actions[ $event_config['a'] ] ) ) {
					continue;
				}
				/** @var TCB_Event_Action_Abstract $action */
				$action                = clone $actions[ $event_config['a'] ];
				$registered_actions [] = array(
					'class'        => $action,
					'event_config' => $event_config,
				);

				if ( ! isset( $javascript_callbacks[ $event_config['a'] ] ) ) {
					$javascript_callbacks[ $event_config['a'] ] = $action->getJsActionCallback();
				}
				if ( ! isset( $registered_javascript_globals[ 'action_' . $event_config['a'] ] ) ) {
					$registered_javascript_globals[ 'action_' . $event_config['a'] ] = $action;
				}
				if ( ! isset( $registered_javascript_globals[ 'trigger_' . $event_config['t'] ] ) ) {
					$registered_javascript_globals[ 'trigger_' . $event_config['t'] ] = $triggers[ $event_config['t'] ];
				}
			}
		}
	}

	if ( empty( $javascript_callbacks ) ) {
		return;
	}

	/* we need to add all the javascript callbacks into the page */
	/* this cannot be done using wp_localize_script WP function, as each if the callback will actually be JS code */
	///euuuughhh

	$GLOBALS['tve_event_manager_callbacks'] = $javascript_callbacks;
	$GLOBALS['tve_event_manager_global_js'] = $registered_javascript_globals;
	$GLOBALS['tve_event_manager_actions']   = $registered_actions;

	/* execute the mainPostCallback on all of the related actions, some of them might need to register stuff (e.g. lightboxes) */
	foreach ( $GLOBALS['tve_event_manager_actions'] as $key => $item ) {
		if ( empty( $item['main_post_callback_'] ) ) {
			$GLOBALS['tve_event_manager_actions'][ $key ]['main_post_callback_'] = true;
			$result                                                              = $item['class']->mainPostCallback( $item['event_config'] );
			if ( is_string( $result ) ) {
				$content .= $result;
			}
		}
	}

	/* remove previously assigned callback, if any - in case of list pages */
	remove_action( 'wp_print_footer_scripts', 'tve_print_footer_events', - 50 );
	add_action( 'wp_print_footer_scripts', 'tve_print_footer_events', - 50 );

}

/**
 * load up all event manager callbacks into the page
 */
function tve_print_footer_events() {
	if ( ! empty( $GLOBALS['tve_event_manager_callbacks'] ) ) {
		echo '<script type="text/javascript">var TVE_Event_Manager_Registered_Callbacks = TVE_Event_Manager_Registered_Callbacks || {};';
		foreach ( $GLOBALS['tve_event_manager_callbacks'] as $key => $js_function ) {
			echo 'TVE_Event_Manager_Registered_Callbacks.' . $key . ' = ' . $js_function . ';';
		}
		echo '</script>';
	}

	if ( ! empty( $GLOBALS['tve_event_manager_triggers'] ) ) {
		echo '<script type="text/javascript">';
		foreach ( $GLOBALS['tve_event_manager_triggers'] as $data ) {
			if ( ! empty( $data['class'] ) && $data['class'] instanceof TCB_Event_Trigger_Abstract ) {
				$js_code = $data['class']->getInstanceJavascript( $data['event_config'] );
				if ( ! $js_code ) {
					continue;
				}
				echo '(function(){' . $js_code . '})();';
			}
		}
		echo '</script>';
	}

	if ( ! empty( $GLOBALS['tve_event_manager_global_js'] ) ) {
		foreach ( $GLOBALS['tve_event_manager_global_js'] as $object ) {
			$object->outputGlobalJavascript();
		}
	}

	if ( ! empty( $GLOBALS['tve_event_manager_actions'] ) ) {
		foreach ( $GLOBALS['tve_event_manager_actions'] as $data ) {
			if ( ! empty( $data['class'] ) && $data['class'] instanceof TCB_Event_Action_Abstract ) {
				echo $data['class']->applyContentFilter( $data['event_config'] );
			}
		}
	}
}

/**
 * fills in some default font data and adds the custom font to the custom fonts list
 *
 * @return array the full array for the added font
 */
function tve_add_custom_font( $font_data ) {
	$custom_fonts = tve_get_all_custom_fonts();

	if ( ! isset( $font_data['font_id'] ) ) {
		$font_data['font_id'] = count( $custom_fonts ) + 1;
	}

	if ( ! isset( $font_data['font_class'] ) ) {
		$font_data['font_class'] = 'ttfm' . $font_data['font_id'];
	}
	if ( ! isset( $font_data['custom_css'] ) ) {
		$font_data['custom_css'] = '';
	}
	if ( ! isset( $font_data['font_color'] ) ) {
		$font_data['font_color'] = '';
	}
	if ( ! isset( $font_data['font_height'] ) ) {
		$font_data['font_height'] = '1.6em';
	}
	if ( ! isset( $font_data['font_size'] ) ) {
		$font_data['font_size'] = '1.6em';
	}
	if ( ! isset( $font_data['font_character_set'] ) ) {
		$font_data['font_character_set'] = 'latin';
	}

	$custom_fonts [] = $font_data;

	update_option( 'thrive_font_manager_options', json_encode( $custom_fonts ) );

	return $font_data;
}

/**
 * Update the image size with, url, width and height
 *
 * @param $image_path
 * @param $template
 * @param $image_source
 */
function tve_update_image_size( $image_path, $template, $image_source ) {
	list( $width, $height ) = getimagesize( $image_path );
	$template = array_merge( $template, array(
		'thumb' => array(
			'url' => $image_source,
			'w'   => $width,
			'h'   => $height,
		),
	) );

	return $template;
}

/**
 * run any necessary code that would be required during an upgrade
 *
 * @param $old_version
 * @param $new_version
 */
function tve_run_plugin_upgrade( $old_version, $new_version ) {
	if ( version_compare( $old_version, '1.74', '<' ) ) {
		/**
		 * refactoring of user templates
		 */
		$user_templates = get_option( 'tve_user_templates', array() );
		$css            = get_option( 'tve_user_templates_styles' );
		$new_templates  = array();
		if ( ! empty( $user_templates ) ) {
			foreach ( $user_templates as $name => $content ) {
				if ( is_array( $content ) ) {
					continue;
				}
				$found            = true;
				$new_templates [] = array(
					'name'    => urldecode( stripslashes( $name ) ),
					'content' => stripslashes( $content ),
					'css'     => isset( $css[ $name ] ) ? trim( stripslashes( $css[ $name ] ) ) : '',
				);
			}
		}

		if ( isset( $found ) ) {
			usort( $new_templates, 'tve_tpl_sort' );
			update_option( 'tve_user_templates', $new_templates );
			delete_option( 'tve_user_templates_styles' );
		}
	}

	if ( version_compare( $old_version, '2.4.8', '<' ) ) {
		$user_templates = get_option( 'tve_user_templates', array() );

		$upload_dir = wp_get_upload_dir();
		if ( ! empty( $upload_dir['basedir'] ) ) {
			foreach ( $user_templates as & $template ) {
				if ( ! isset( $template['thumb'] ) && isset( $template['image_url'] ) ) {
					$image_path = trailingslashit( $upload_dir['basedir'] ) . 'thrive-visual-editor/user_templates/' . basename( $template['image_url'] );

					$template = tve_update_image_size( $image_path, $template, $template['image_url'] );

					$do_update = true;
					unset( $template['image_url'] );
				}
			}
			unset( $template );
		}

		if ( isset( $do_update ) ) {
			update_option( 'tve_user_templates', $user_templates );
		}
	}
}

/**
 * determine whether the user is on the editor page or not (also takes into account edit capabilities)
 *
 * @return bool
 */
function is_editor_page() {
	/**
	 * during AJAX calls, we need to apply a filter to get this value, we cannot rely on the traditional detection
	 */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$is_editor_page = apply_filters( 'tcb_is_editor_page_ajax', false );
		if ( $is_editor_page ) {
			return true;
		}
	}

	if ( apply_filters( 'tcb_is_inner_frame_override', false ) ) {
		return true;
	}

	if ( is_admin() ) {
		return false;
	}

	if ( ! apply_filters( 'tcb_is_editor_page', (bool) get_the_ID() ) ) {
		return false;
	}

	return isset( $_GET[ TVE_EDITOR_FLAG ] ) && TCB_Product::has_external_access( get_the_ID() ) && tve_membership_plugin_can_display_content();
}

/**
 * check if there is a valid activated license for the TCB plugin
 *
 * @return bool
 */
function tve_tcb__license_activated() {
	return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TCB_TAG );
}

/**
 * determine whether the user is on the editor page or not based just on a $_GET parameter
 * modification: WP 4 removed the "preview" parameter
 *
 * @param bool $check_ajax_request whether or not to also return true for ajax requests made from the editor page
 *
 * @return bool
 */
function is_editor_page_raw( $check_ajax_request = false ) {
	/**
	 * during AJAX calls, we need to apply a filter to get this value, we cannot rely on the traditional detection
	 */
	$is_rest_ajax = ! empty( $_REQUEST['tar_editor_page'] ) && defined( 'REST_REQUEST' ) && REST_REQUEST;

	if ( $is_rest_ajax || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		$is_editor_ajax = $check_ajax_request && ! empty( $_REQUEST['tar_editor_page'] );

		if ( apply_filters( 'tcb_is_editor_page_raw_ajax', $is_editor_ajax ) ) {
			return true;
		}
	}

	return isset( $_GET[ TVE_EDITOR_FLAG ] );
}

/**
 * Removes the theme CSS from the architect page
 */
function tve_remove_theme_css() {
	global $wp_styles;

	$theme          = get_template();
	$stylesheet_dir = basename( get_stylesheet_directory() );

	foreach ( $wp_styles->queue as $handle ) {
		$src = $wp_styles->registered[ $handle ]->src;
		if ( apply_filters( 'tcb_remove_theme_css', strpos( $src, $theme ) !== false || strpos( $src, $stylesheet_dir ) !== false, $src ) ) {
			wp_deregister_style( $handle );
		}
	}
}

/**
 * only enqueue scripts on our own editor pages
 */
function tve_enqueue_editor_scripts() {
	if ( is_editor_page() && tve_is_post_type_editable( get_post_type( get_the_ID() ) ) ) {

		/**
		 * the constant should be defined somewhere in wp-config.php file
		 */
		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		/**
		 * this is to handle the following case: an user who has the TL plugin (or others) installed, TCB installed and enabled, but TCB license is expired
		 * in this case, users should still be able to edit stuff from outside the TCB plugin, such as forms
		 */
		if ( tve_tcb__license_activated() || apply_filters( 'tcb_skip_license_check', false ) ) {
			$post_id = get_the_ID();

			/**
			 * apply extra filters that should check if the user can actually use the editor to edit this particular piece of content
			 */
			if ( apply_filters( 'tcb_user_can_edit', true, $post_id ) ) {

				global $tve_style_family_classes;

				// Thrive Architect javascript file (loaded both frontend and backend).
				tve_enqueue_script( 'tve_frontend', tve_editor_js() . '/frontend' . $js_suffix, array(
					'jquery',
				), false, true );

				/**
				 * enqueue resizable for older WP versions
				 */
				wp_enqueue_script( 'jquery-ui-resizable' );

				tve_enqueue_script( 'tcb-froala', tve_editor_url( 'editor/js/dist/froala' . $js_suffix ), array( 'tve_editor' ) );

				/** control panel scripts and dependencies */
				tve_enqueue_script( 'tve_editor', tve_editor_js() . '/editor' . $js_suffix, array(
					'jquery',
					'jquery-ui-autocomplete',
					'jquery-ui-slider',
					'jquery-ui-resizable',
					'underscore',
				), false, true );

				// Enqueue dom-to-image script. Used for generation of the images
				wp_enqueue_script( 'tcb-dom-to-image', tve_editor_url() . '/editor/js/libs/dom-to-image' . $js_suffix, array( 'tve_editor' ) );

				// Enqueue lazyload script. Used for lazyloading images
				wp_enqueue_script( 'tcb-lazyload', tve_editor_url() . '/editor/js/libs/lazyload.min.js', array( 'tve_editor' ) );

				// jQuery UI stuff
				// no need to append TVE_VERSION for these scripts
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-serialize-object' );
				wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
				wp_enqueue_script( 'jquery-ui-autocomplete' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-ui-slider', array( 'jquery', 'jquery-ui-core' ) );

				wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );

				/*script needed to load the VooPlayer videos*/
				wp_enqueue_script( 'vooplayer_script', 'https://s3.spotlightr.com/assets/vooplayer.js', array(), '', false );

				// now enqueue the styles
				tve_enqueue_style( 'tve_editor_style', tve_editor_css() . '/editor.css' );
				tve_enqueue_style( 'tve_inner_style', tve_editor_css() . '/editor/style.css' );

				// load style family
				$loaded_style_family = tve_get_style_family( $post_id );

				// custom fonts from Font Manager
				$all_fonts         = tve_get_all_custom_fonts();
				$all_fonts_enqueue = apply_filters( 'tve_filter_custom_fonts_for_enqueue_in_editor', $all_fonts );
				tve_enqueue_fonts( $all_fonts_enqueue );

				$post_type = get_post_type( get_the_ID() );
				/**
				 * we need to enforce this check here, so that we don't make http requests from https pages
				 */
				$admin_base_url = admin_url( '/', is_ssl() ? 'https' : 'admin' );
				// for some reason, the above line does not work in some instances
				if ( is_ssl() ) {
					$admin_base_url = str_replace( 'http://', 'https://', $admin_base_url );
				}

				/**
				 * Get all dynamic links available
				 */
				$dynamic_links = apply_filters( 'tcb_dynamiclink_data', array() );

				/* add the 'Content' and 'Custom Fields' keys at the end of the array */
				$dynamic_links                = array_merge( $dynamic_links, array(
					'Content'       => array(),
					'Custom Fields' => array(),
					'Shortcode'     => array(),
				) );
				$hidden_dynamic_links_options = array( 'Custom Fields Global' ); //Hide options but keep their data

				$author_social_url = tve_author_social_url();

				// pass variables needed to client side
				$tve_path_params = array(
					'admin_url'                     => $admin_base_url,
					'site_url'                      => site_url(),
					'cpanel_dir'                    => tve_editor_url() . '/editor',
					'shortcodes_dir'                => tve_editor_url() . '/shortcodes/templates/',
					'editor_dir'                    => tve_editor_css(),
					'style_families'                => tve_get_style_families(),
					'style_classes'                 => $tve_style_family_classes,
					'loaded_style'                  => $loaded_style_family,
					'post_id'                       => get_the_ID(),
					'post_url'                      => get_permalink( get_the_ID() ),
					'tve_version'                   => TVE_VERSION,
					'tve_loaded_stylesheet'         => $loaded_style_family,
					'ajax_url'                      => $admin_base_url . 'admin-ajax.php',
					'is_rtl'                        => (int) is_rtl(),
					'woocommerce'                   => \Tcb\Integrations\WooCommerce\Main::get_localized_data(),
					'custom_fonts'                  => $all_fonts,
					'post_type'                     => $post_type,
					'queried_object'                => get_queried_object(),
					'taxonomies'                    => get_object_taxonomies( 'post', 'object' ),
					'post_types'                    => tve_get_regular_post_types(),
					'date_format'                   => get_option( 'date_format' ),
					'time_format'                   => get_option( 'time_format' ),
					'routes'                        => array(
						'posts' => get_rest_url( get_current_blog_id(), 'tcb/v1' . '/posts' ),
					),
					'dynamic_image_placeholders'    => array(
						'featured' => TCB_Post_List_Featured_Image::get_default_url(),
						'author'   => TCB_Post_List_Author_Image::get_default_url(),
					),
					'post_list_pagination'          => TCB_Utils::get_pagination_localized_data(),
					'post_image'                    => array(
						'featured' => TCB_Post_List_Featured_Image::get_default_url( get_the_ID() ),
						'author'   => TCB_Post_List_Author_Image::get_default_url( get_the_ID() ),
					),
					'featured_image'                => array(
						'default_sizes'  => array_keys( TCB_Post_List_Featured_Image::filter_available_sizes() ),
						'image_subsizes' => TCB_Post_List_Featured_Image::get_registered_image_subsizes(),
					),
					// this is to allow overriding the default save_post action ajax callback,
					'tve_display_save_notification' => (int) get_option( 'tve_display_save_notification', 1 ),
					'dynamic_links'                 => $dynamic_links,
					'dynamic_links_categories'      => array_values( array_diff( array_keys( $dynamic_links ), $hidden_dynamic_links_options ) ),
					/**
					 * Each element in the array should be a group ( group name = key, array of values ) :
					 * 'Group Name' => array(
					 *     array(
					 *         'name'   => 'VisibleName', // ?? visible name in editing mode
					 *         'value'  => 'shortcode_tag', // shortcode tag that's rendered in the page
					 *         'option' => 'Shortcode Title', // shortcode title - title displayed in the menu
					 *     ),
					 * ),
					 *
					 * !Important note: When adding new groups on this filter, also add them in SHORTCODE_GROUP_ORDER_MAP in JS in order to specify where it should show up
					 */
					'inline_shortcodes'             => apply_filters( 'tcb_inline_shortcodes', array() ),
					'external_custom_fields'        => tcb_custom_fields_api()->get_all_external_fields(),
					'tve_fa_kit'                    => get_option( 'tve_fa_kit', '' ),
					'tve_icon_api'                  => TVE_ICON_API,
					'author_social_links'           => $author_social_url,
				);

				$tve_path_params = apply_filters( 'tcb_editor_javascript_params', $tve_path_params, $post_id, $post_type );

				wp_localize_script( 'tve_editor', 'tve_path_params', $tve_path_params );

				/* some params will be needed also for the frontend script */
				$frontend_options = array(
					'is_editor_page'   => true,
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'social_fb_app_id' => tve_get_social_fb_app_id(),
					'is_single'        => (string) ( (int) is_singular() ),
				);

				/**
				 * Allows adding frontend options from different plugins
				 *
				 * @param $frontend_options
				 */
				$frontend_options = apply_filters( 'tve_frontend_options_data', $frontend_options );

				wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );

				do_action( 'tcb_editor_enqueue_scripts' );
			}
		} else {
			add_action( 'wp_print_footer_scripts', 'tve_license_notice' );
		}
	}
}

/**
 * Return the regular post types without the blacklisted ones.
 *
 * @return array
 */
function tve_get_regular_post_types() {
	$ignored_types = apply_filters( 'thrive_ignored_post_types', array(
		'attachment',
		'tcb_lightbox',
		'tcb_symbol',
	) );

	$all = get_post_types( array( 'public' => true ) );

	$post_types = array();

	foreach ( $all as $key => $post_type ) {
		if ( in_array( $key, $ignored_types, true ) ) {
			continue;
		}

		$post_types[ $key ] = array(
			'label'        => tvd_get_post_type_label( $key ),
			'plural_label' => get_post_type_object( $key )->labels->name,
		);
	}

	return $post_types;
}

/**
 * enqueue the associated style family for a post / page
 *
 * this also gets called in archive (list) pages, there we need to load style families for each post from the list
 *
 * @param null $post_id optional this will only come filled in when calling it from a lightbox
 */
function tve_enqueue_style_family( $post_id = null ) {
	global $tve_style_family_classes, $wp_query;
	$tve_style_families = tve_get_style_families();

	if ( null === $post_id ) {
		$posts_to_load = $wp_query->posts;
		if ( empty( $posts_to_load ) || ! is_array( $posts_to_load ) ) {
			return;
		}
		$post_id = array();
		foreach ( $posts_to_load as $post ) {
			$post_id [] = $post->ID;
		}
	} else {
		$post_id = array( $post_id );
	}

	foreach ( $post_id as $p_id ) {
		$current_post_style = tve_get_style_family( $p_id );

		$style_key = 'tve_style_family_' . strtolower( $tve_style_family_classes[ $current_post_style ] );
		if ( ! wp_style_is( $style_key ) ) {
			tve_enqueue_style( $style_key, $tve_style_families[ $current_post_style ] );
		}
	}
}

/**
 * retrieve the style family used for a specific post / page
 *
 * @param        $post_id
 * @param string $default
 */
function tve_get_style_family( $post_id, $default = 'Flat' ) {
	$tve_style_families = tve_get_style_families();
	$current_post_style = get_post_meta( $post_id, 'tve_style_family', true );

	// Flat is default style family if nothing set
	$current_post_style = empty( $current_post_style ) || ! isset( $tve_style_families[ $current_post_style ] ) ? $default : $current_post_style;

	return $current_post_style;
}

/**
 * get the css class for a style family
 *
 * @param int $post_id
 *
 * @return string
 */
function tve_get_style_family_class( $post_id ) {
	global $tve_style_family_classes;
	$style_family = get_post_meta( $post_id, 'tve_style_family', true );

	return ! empty( $style_family ) && isset( $tve_style_family_classes[ $style_family ] ) ? $tve_style_family_classes[ $style_family ] : $tve_style_family_classes['Flat'];
}


/**
 * Returns all global style option names depending on the element type
 *
 * @return array
 */
function tve_get_global_styles_option_names() {
	return apply_filters( 'tcb_global_styles_option_name', array(
		'button'     => 'tve_global_button_styles',
		'section'    => 'tve_global_section_styles',
		'contentbox' => 'tve_global_contentbox_styles',
		'link'       => 'tve_global_link_styles',
		'text'       => 'tve_global_text_styles',
	) );
}

/**
 * Constructs the shared styles css
 *
 * @param string  $post_content
 * @param string  $for_media
 * @param boolean $editor_ajax_check optional. Controls whether or not to output global styles in ajax requests in the editor page.
 *
 * @return string
 */
function tve_get_shared_styles( $post_content = '', $for_media = '', $editor_ajax_check = true ) {
	$for_media = (string) $for_media;
	/**
	 * Makes sure global styles are not loaded into editor page via ajax
	 */
	if ( $editor_ajax_check && wp_doing_ajax() && is_editor_page_raw( true ) ) {
		return '';
	}
	/**
	 * Makes sure global styles are only loaded once in the editor page
	 */
	if ( ! wp_doing_ajax() && tcb_editor()->is_inner_frame() && ! doing_action( 'wp_head' ) ) {
		return '';
	}

	$output        = '';
	$shared_styles = array();

	$global_style_options = tve_get_global_styles_option_names();

	$button_styles  = get_option( $global_style_options['button'], array() );
	$section_styles = get_option( $global_style_options['section'], array() );
	$cb_styles      = get_option( $global_style_options['contentbox'], array() );
	$link_styles    = get_option( $global_style_options['link'], array() );
	$text_styles    = get_option( $global_style_options['text'], array() );

	$styles_types = apply_filters( 'tcb_get_extra_global_styles', array( $button_styles, $section_styles, $cb_styles, $link_styles, $text_styles ) );


	$is_editor_page = is_editor_page_raw( true );

	if ( $is_editor_page ) {
		$post_content = '';
	}

	if ( empty( $GLOBALS['tve_parsed_shared_styles'] ) ) {
		/* so we won't render the same shared style multiple times */
		$GLOBALS['tve_parsed_shared_styles'] = array();
	}

	foreach ( $styles_types as $style_type ) {
		if ( ! is_array( $style_type ) ) {
			$style_type = array();
		}

		foreach ( $style_type as $identifier => $styles ) {
			if ( empty( $styles['css'] ) ) {
				/**
				 * Security check
				 */
				continue;
			}

			if ( in_array( $identifier, $GLOBALS['tve_parsed_shared_styles'] ) || (
					! empty( $post_content ) &&
					( strpos( $post_content, TVE_GLOBAL_STYLE_CLS_PREFIX ) === false || strpos( $post_content, $identifier ) === false ) )
			) {
				continue;
			}

			$GLOBALS['tve_parsed_shared_styles'][] = $identifier;

			foreach ( $styles['css'] as $media => $css ) {
				if ( empty( $shared_styles[ $media ] ) ) {
					$shared_styles[ $media ] = array();
				}
				$shared_styles[ $media ] = array_merge( $shared_styles[ $media ], $css );
			}

			if ( empty( $styles['fonts'] ) ) {
				/**
				 * Security check
				 */
				continue;
			}

			foreach ( $styles['fonts'] as $i => $import_rule ) {
				$output .= $import_rule;
			}
		}
	}

	/**
	 * Default styles should only be printed in this node in the editor page, and only if a landing page allows it (if editing a landing page)
	 */
	$output_default_styles = $is_editor_page;
	if ( $output_default_styles && tcb_post()->is_landing_page() && tcb_landing_page( get_the_ID() )->should_strip_head_css() ) {
		$output_default_styles = false;
	}

	/**
	 * The filter allows including / excluding shared styles from the current piece of content
	 *
	 * @param bool $output_default_styles
	 */
	$output_default_styles = apply_filters( 'tcb_output_default_styles', $output_default_styles );
	if ( $output_default_styles ) {
		/* Make sure default styles are inserted before the global styles */
		$default_styles = tve_prepare_default_styles();
		$output         .= implode( "", $default_styles['@imports'] );
		foreach ( $default_styles['media'] as $media_key => $css_str ) {
			if ( ! isset( $shared_styles[ $media_key ] ) ) {
				$shared_styles[ $media_key ] = array( $css_str );
			} else {
				array_unshift( $shared_styles[ $media_key ], $css_str );
			}
		}
	}

	foreach ( $shared_styles as $media => $css_array ) {
		if ( ! empty( $for_media ) && strpos( $media, $for_media ) === false ) {
			/**
			 * If for media parameter is defined it will output only the css for that particular media
			 */
			continue;
		}

		$output .= '@media' . $media . '{';
		foreach ( $css_array as $css_item ) {
			$output .= $css_item;
		}
		$output .= '}';
	}

	$global_selector = tcb_selection_root();
	$selector        = apply_filters( 'tcb_global_styles_selector', $global_selector );
	$output          = str_replace( $global_selector, $selector, $output );

	if ( ! empty( $output ) || $is_editor_page ) {
		$output = sprintf( '<style type="text/css" class="tve_global_style">%s</style>', tve_prepare_global_variables_for_front( stripslashes( $output ) ) );
	}

	return $output;
}

/**
 * Loads user defined custom css in the header to override style family css
 * If called with $post_id != null, it will load the custom css and user custom css from inside the loop (in case of homepage consisting of other pages, for example)
 */
function tve_load_custom_css( $post_id = null ) {
	if ( is_feed() ) {
		return;
	}
	if ( ! is_null( $post_id ) ) {

		/**
		 * Outputs the shared styles css
		 */
		echo tve_get_shared_styles( tve_get_post_meta( $post_id, 'tve_updated_post' ) );

		$custom_css = trim( tve_get_post_meta( $post_id, 'tve_custom_css', true ) . tve_get_post_meta( $post_id, 'tve_user_custom_css', true ) );
		if ( $custom_css ) {
			$custom_css = tve_prepare_global_variables_for_front( $custom_css );
			echo sprintf(
				'<style type="text/css" class="tve_custom_style">%s</style>',
				tcb_custom_css( $custom_css )
			);
		}

		return;
	}
	global $wp_query;
	$posts_to_load = $wp_query->posts;

	global $css_loaded_post_id;
	$css_loaded_post_id = array();

	/* user-defined css from the Custom CSS content element */
	$user_custom_css = '';
	if ( $posts_to_load ) {

		$inline_styles = '';
		$post_content  = '';
		foreach ( $posts_to_load as $post ) {
			$inline_styles   .= tve_get_post_meta( $post->ID, 'tve_custom_css', true );
			$user_custom_css .= tve_get_post_meta( $post->ID, 'tve_user_custom_css', true );
			array_push( $css_loaded_post_id, $post->ID );

			$post_content .= tve_get_post_meta( $post->ID, 'tve_updated_post' );
		}

		if ( ! empty( $post_content ) || is_editor_page() ) {
			/**
			 * Outputs the shared styles css
			 */
			echo tve_get_shared_styles( $post_content );
		}

		if ( ! empty( $inline_styles ) ) {
			$inline_styles = tve_prepare_global_variables_for_front( $inline_styles );
			?>
			<style type="text/css" class="tve_custom_style"><?php echo tcb_custom_css( $inline_styles ); ?></style>
			<?php
		}
		/* also check for user-defined custom CSS inserted via the "Custom CSS" content editor element */
		echo $user_custom_css ? sprintf( '<style type="text/css" id="tve_head_custom_css" class="tve_user_custom_style">%s</style>', $user_custom_css ) : '';
	}
	/**
	 * Action triggered to load more css or force css that wasnt loaded by TAR
	 * e.g 404 templates
	 */
	do_action( 'tve_after_load_custom_css' );
}

/**
 * checks to see if content being loaded is actually being loaded from within the loop (correctly) or being pulled
 * incorrectly to make up another page (for instance, a homepage that pulls different sections from pieces of content)
 */
function tve_check_in_loop( $post_id ) {
	global $css_loaded_post_id;
	if ( ! empty( $css_loaded_post_id ) && in_array( $post_id, $css_loaded_post_id ) ) {
		return true;
	}

	return false;
}

/**
 * replace [tcb-script] with script tags
 *
 * @param array $matches
 *
 * @return string
 */
function tve_restore_script_tags_replace( $matches ) {
	$matches[2] = str_replace( '<\\/script', '<\\\\/script', $matches[2] );

	return '<script' . $matches[1] . '>' . html_entity_decode( $matches[2] ) . '</script>';
}

/**
 * replace [tcb-noscript] with <noscript> tags
 *
 * @param array $matches
 *
 * @return string
 */
function tve_restore_script_tags_noscript_replace( $matches ) {
	return '<noscript' . $matches[1] . '>' . html_entity_decode( $matches[2] ) . '</noscript>';
}

/**
 * restore all script tags from custom html controls. script tags are replaced with <code class="tve_js_placeholder">
 *
 * @param string $content
 *
 * @return string having all <code class="tve_js_placeholder">..</code> replaced with their script tag equivalent
 */
function tve_restore_script_tags( $content ) {
	$shortcode_js_pattern = '/\[tcb-script(.*?)\](.*?)\[\/tcb-script\]/s';
	$content              = preg_replace_callback( $shortcode_js_pattern, 'tve_restore_script_tags_replace', $content );

	$shortcode_nojs_pattern = '/\[tcb-noscript(.*?)\](.*?)\[\/tcb-noscript\]/s';
	$content                = preg_replace_callback( $shortcode_nojs_pattern, 'tve_restore_script_tags_noscript_replace', $content );

	return $content;
}

/**
 * get a list of all published Thrive Opt-Ins post types
 *
 * @return array pairs id => title
 */
function tve_get_thrive_optins() {
	$optins = array();

	$args = array(
		'posts_per_page' => null,
		'numberposts'    => null,
		'post_type'      => 'thrive_optin',
	);

	foreach ( get_posts( $args ) as $post ) {
		$optins[ $post->ID ] = $post->post_title;
	}

	return $optins;
}

/**
 * Thrive Shortcode callback that will call apply_filters on "tve_additional_fields" tag
 *
 * @param array $data with [group_id, form_type_id, variation_id]
 *
 * @return mixed
 * @see tve_thrive_shortcodes
 *
 */
function tve_leads_additional_fields_filters( $data ) {
	$group     = $data['group_id'];
	$form_type = $data['form_type_id'];
	$variation = $data['variation_id'];

	if ( ! empty( $form_type ) && function_exists( 'tve_leads_get_form_type' ) ) {
		$form_type = tve_leads_get_form_type( $form_type, array( 'get_variations' => false ) );
		if ( $form_type && $form_type->post_parent ) {
			$group = get_post( $form_type->post_parent );
		}
	}

	if ( ! empty( $variation ) && function_exists( 'tve_leads_get_form_variation' ) ) {
		$variation = tve_leads_get_form_variation( null, $variation );
		if ( ! empty( $variation['parent_id'] ) ) {
			$variation = tve_leads_get_form_variation( null, $variation['parent_id'] );
		}
	}

	return apply_filters( 'tve_additional_fields', '', $group, $form_type, $variation );
}

/**
 * parse content for configuration that belongs to theme-equivalent shortcodes, e.g. Opt-in shortcode
 *
 * for each key from $tve_thrive_shortcodes, it will search the content string for __CONFIG_{$key}__(.+)__CONFIG_{$key}__
 * if elements are found, the related callback will be called with the contents from between the two flags (this is a json_encoded string)
 *
 * shortcode configuration is held in JSON-encoded format inside a hidden div
 * these contents will get deleted if we're currently NOT in editor mode
 *
 * @param string $content
 * @param bool   $keep_config
 *
 * @return string
 */
function tve_thrive_shortcodes( $content, $keep_config = false ) {
	global $tve_thrive_shortcodes;

	$shortcode_pattern = '#>__CONFIG_%s__(.+?)__CONFIG_%s__</div>#';

	/* old thrive theme shortcodes */
	$theme_shortcodes = array( 'optin', 'posts_list', 'custom_menu', 'custom_phone' );

	foreach ( $tve_thrive_shortcodes as $shortcode => $callback ) {
		if ( ! tve_check_if_thrive_theme() && in_array( $shortcode, $theme_shortcodes, true ) ) {
			continue;
		}

		if ( ! function_exists( $callback ) ) {
			continue;
		}

		/**
		 * we dont want to apply this shortcode if $keep_config is true => is_editor
		 */
		if ( $shortcode === 'tve_leads_additional_fields_filters' && $keep_config === true ) {
			continue;
		}
		/*
         * match all instances of the current shortcode
         */
		if ( preg_match_all( sprintf( $shortcode_pattern, $shortcode, $shortcode ), $content, $matches, PREG_OFFSET_CAPTURE ) !== false ) {
			/* as we go over the $content and replace each shortcode, we must take into account the differences of replacement length and the length of the part getting replaced */
			$position_delta = 0;
			foreach ( $matches[1] as $i => $data ) {
				$m           = $data[0]; // the actual matched regexp group
				$position    = $matches[0][ $i ][1] + $position_delta; //the index at which the whole group starts in the string, at the current match
				$whole_group = $matches[0][ $i ][0];
				$json_safe   = tve_json_utf8_slashit( $m );
				if ( ! ( $_params = @json_decode( $json_safe, true ) ) ) {
					$_params = array();
				}

				/* If the shortcode was already rendered, we render empty sting instead of the actual content */
				$replacement = empty( $_params['tve_shortcode_rendered'] ) ? call_user_func( $callback, $_params, $keep_config ) : '';

				/* Flag to mark the fact that this shortcode was showed */
				$_params['tve_shortcode_rendered'] = 1;
				$m                                 = tve_json_utf8_unslashit( json_encode( $_params ) );

				$replacement = ( $keep_config ? ">__CONFIG_{$shortcode}__{$m}__CONFIG_{$shortcode}__</div>" : '></div>' ) . $replacement;

				$content = substr_replace( $content, $replacement, $position, strlen( $whole_group ) );
				/* increment the positioning offsets for the string with the difference between replacement and original string length */
				$position_delta += strlen( $replacement ) - strlen( $whole_group );

			}
		}
	}

	// we include the wistia js only if wistia popover responsive video is added to the content (div with class tve_wistia_popover)
	if ( ! $keep_config && strpos( $content, 'tve_wistia_popover' ) !== false ) {
		wp_script_is( 'tl-wistia-popover' ) || wp_enqueue_script( 'tl-wistia-popover', '//fast.wistia.com/assets/external/E-v1.js', array(), '', true );
	}

	// we include the vooplayer js only if vooplayer video responsive or a custom field video is added to the content
	if ( strpos( $content, 'vooplayer' ) !== false || strpos( $content, 'thrv_responsive_video thrv_wrapper tcb-custom-field-source' ) !== false ) {
		wp_enqueue_script( 'vooplayer_script', 'https://s3.spotlightr.com/assets/vooplayer.js', array(), '', false );
	}

	if ( ! $keep_config ) {
		$content = preg_replace( '/\s*<div class="(thrive-shortcode|widget)-config" style="display:\s?none;?"><\/div>\s*/', '', $content );
	}

	/**
	 * Allows dynamically modifying any piece of TAr content right before the TAr shortcodes are parsed and replaced
	 *
	 * @param string $content     content being processed
	 * @param bool   $keep_config whether this is for editor pages or frontend
	 *
	 * @return string
	 */
	$content = apply_filters( 'tve_thrive_shortcodes', $content, $keep_config );

	return $content;
}

/**
 * Render post grid shortcode
 * Called from shortcode parser and when user drags element into page
 */
function tve_do_post_grid_shortcode( $config ) {

	require_once dirname( dirname( __FILE__ ) ) . '/inc/classes/class-tcb-post-grid.php';
	$post_grid = new TCB_Post_Grid( $config );

	$post_grid->output_shortcode_config = false;

	return $post_grid->render();
}

/**
 * Submits the Architect Contact Form
 * Called from the submit button from each Contact Form
 */
function tve_submit_contact_form() {

	$posted_data = (array) $_POST;
	$posted_data = array_diff_key( $posted_data, array( 'action' => '' ) );

	require_once dirname( dirname( __FILE__ ) ) . '/inc/classes/class-tcb-contact-form.php';
	$contact_form = new TCB_Contact_Form( $posted_data );

	$response = $contact_form->submit();

	if ( 0 === $response['success'] ) {
		wp_send_json_error( $response );
	}

	wp_send_json_success( $response );
}

/**
 * Render symbol shortcode
 *
 * @param array $config
 *
 * @return string
 */
function tcb_symbol_shortcode( $config ) {
	return TCB_Symbol_Template::symbol_render_shortcode( $config );
}

/**
 * handle the Opt-In shortcode from the themes
 * at this point this just forwards the call to the theme's Opt-In shortcode
 *
 * @param array $attrs
 *
 * @return string
 */
function tve_do_optin_shortcode( $attrs ) {
	return '<div class="thrive-shortcode-html">' . thrive_shortcode_optin( $attrs, '' ) . '</div>';
}

/**
 * handle the posts lists shortcode from the themes.  Full docs in function tve_do_optin_shortcode comments
 *
 * @param $attrs
 *
 * @return string
 */
function tve_do_posts_list_shortcode( $attrs ) {
	return '<div class="thrive-shortcode-html">' . thrive_shortcode_posts_list( $attrs, '' ) . '</div>';
}

/**
 * handle the leads shortcode
 *
 * @param $attr
 *
 * @return string
 */
function tve_do_leads_shortcode( $attrs ) {
	if ( is_feed() ) {
		return '';
	}
	$error_content = '<div class="thrive-shortcode-html"><p>' . __( 'Thrive Leads Shortcode could not be rendered, please check it in Thrive Leads Section!', 'thrive-cb' ) . '</p></div>';
	if ( ! function_exists( 'tve_leads_shortcode_render' ) ) {
		return $error_content;
	}

	if ( is_editor_page() ) {
		$attrs['for_editor'] = true;
		$content             = tve_leads_shortcode_render( $attrs );
		$content             = ! empty( $content['html'] ) ? $content['html'] : '';
	} else {
		$content = tve_leads_shortcode_render( $attrs );
	}

	if ( empty( $content ) ) {
		return $error_content;
	}

	return '<div class="thrive-shortcode-html">' . str_replace( 'tve_editor_main_content', '', $content ) . '</div>';
}

/**
 * handle the custom menu shortcode
 *
 * @param $atts
 *
 * @return string
 */
function tve_do_custom_menu_shortcode( $atts ) {
	return '<div class="thrive-shortcode-html">' . thrive_shortcode_custom_menu( $atts, '' ) . '</div>';
}

/**
 * handle the custom phone shortcode
 *
 * @param $atts
 *
 * @return string
 */
function tve_do_custom_phone_shortcode( $atts ) {
	return '<div class="thrive-shortcode-html">' . thrive_shortcode_custom_phone( $atts, '' ) . '</div>';
}

/**
 * mimics all wordpress called functions when rendering a shortcode
 *
 * @param $content
 */
function tcb_render_wp_shortcode( $content ) {

	$do_shortcode = is_editor_page() || ( defined( 'DOING_AJAX' ) && DOING_AJAX );

	/* fix for SUPP-5168, treat [embed] shortcodes separately by delegating the shortcode function to class-wp-embed.php */
	if ( $do_shortcode ) {
		$content = tve_handle_embed_shortcode( $content );
	}
	/**
	 * This makes sure that the content doesn't contain any left-over <!-- gutenberg --> tags
	 */
	if ( function_exists( 'do_blocks' ) ) {
		$content = do_blocks( $content );
	}
	$content = wptexturize( ( $content ) );
	$content = convert_smilies( $content );
	$content = convert_chars( $content );

	$content = shortcode_unautop( $content );
	$content = shortcode_unautop( wptexturize( $content ) );

	if ( $do_shortcode ) {
		$content = preg_replace( '#<!--more(.*?)-->#', '<span class="tcb-wp-more-tag"></span>', $content );
	}

	return $do_shortcode ? do_shortcode( $content ) : $content;
}

/**
 * replace all the {tcb_post_} shortcodes with actual values
 *
 * @param $content
 */
function tve_do_custom_content_shortcodes( $content ) {
	/**
	 * if we are currently redering a TCB lightbox, we still need to have the main post title, url etc
	 */
	if ( ! empty( $GLOBALS['tcb_main_post_lightbox'] ) ) {
		$post_id = $GLOBALS['tcb_main_post_lightbox']->ID;
	} else {
		$post_id = get_the_ID();
	}
	$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
	$permalink      = get_permalink( $post_id );
	$search         = array(
		'{tcb_post_url}',
		'{tcb_encoded_post_url}',
		'{tcb_post_title}',
		'{tcb_post_image}',
		'{tcb_current_year}',
	);
	$replace        = array(
		$permalink,
		urlencode( $permalink ),
		get_the_title( $post_id ),
		! empty( $featured_image ) && ! empty( $featured_image[0] ) ? $featured_image[0] : '',
		date( 'Y' ),
	);
	$content        = str_replace( $search, $replace, $content );

	return $content;
}

/**
 * render any shortcodes that might be included in the post meta-data using the Insert Shortcode element
 * raw shortcode texts are saved between 2 flags: ___TVE_SHORTCODE_RAW__ AND __TVE_SHORTCODE_RAW___
 *
 * @param string $content
 * @param bool   $is_editor_page
 */
function tve_do_wp_shortcodes( $content, $is_editor_page = false ) {
	if ( ! $is_editor_page ) {
		$content = tve_do_custom_content_shortcodes( $content );
	}

	$allowed_shortcodes = apply_filters( 'tcb_content_allowed_shortcodes', array(), $is_editor_page );

	if ( ! empty( $allowed_shortcodes ) ) {
		$pattern = get_shortcode_regex( $allowed_shortcodes );
		$content = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $content );
	}

	list( $start, $end ) = array(
		'___TVE_SHORTCODE_RAW__',
		'__TVE_SHORTCODE_RAW___',
	);
	if ( strpos( $content, $start ) === false ) {
		return $content;
	}
	if ( ! preg_match_all( "/{$start}((<p>)?(.+?)(<\/p>)?){$end}/s", $content, $matches, PREG_OFFSET_CAPTURE ) ) {
		return $content;
	}

	$position_delta = 0;
	foreach ( $matches[1] as $i => $data ) {
		$raw_shortcode = $data[0]; // the actual matched regexp group
		$position      = $matches[0][ $i ][1] + $position_delta; //the index at which the whole group starts in the string, at the current match
		$whole_group   = $matches[0][ $i ][0];

		$raw_shortcode = html_entity_decode( $raw_shortcode );//we keep the code encoded and now we need to decode

		$replacement = tcb_render_wp_shortcode( $raw_shortcode );

		$replacement = ( $is_editor_page ? $whole_group : '' ) . ( '</div><div class="tve_shortcode_rendered">' . $replacement );
		$content     = substr_replace( $content, $replacement, $position, strlen( $whole_group ) );
		/* increment the positioning offsets for the string with the difference between replacement and original string length */
		$position_delta += strlen( $replacement ) - strlen( $whole_group );
	}

	return $content;
}

/**
 * check if post having id $id is a landing page created with TCB
 *
 * @param $id
 *
 * @return Boolean
 */
function tve_post_is_landing_page( $id = 0 ) {

	if ( empty( $id ) && ! is_singular() ) {
		return false;
	}

	if ( empty( $id ) ) {
		$id = get_the_ID();
	}

	$is_landing_page = get_post_meta( $id, 'tve_landing_page', true );

	if ( ! $is_landing_page ) {
		return false;
	}

	return $is_landing_page; // this is the actual landing page template
}

/**
 * get post meta key. Also takes into account whether or not this post is a landing page
 * each regular meta key from the editor has the associated meta key for the landing page constructed by appending a "_{template_name}" after the key
 *
 * @param int    $post_id
 * @param string $meta_key
 *
 * @return string
 */
function tve_get_post_meta( $post_id, $meta_key, $single = true ) {
	if ( ( $template = tve_post_is_landing_page( $post_id ) ) !== false ) {
		$meta_key = $meta_key . '_' . $template;
	}

	$value = get_post_meta( $post_id, $meta_key, $single );

	/**
	 * I'm not sure why this is happening, but we had some instances where these meta values were being serialized twice
	 */
	if ( $single ) {
		$value = maybe_unserialize( $value );
	}

	return $value;
}

/**
 * update a post meta key. Also takes into account whether or not this post is a landing page
 * each regular meta key from the editor has the associated meta key for the landing page constructed by appending a "_{template_name}" after the key
 *
 * @param $post_id
 * @param $meta_key
 * @param $value
 */
function tve_update_post_meta( $post_id, $meta_key, $meta_value ) {
	if ( ( $template = tve_post_is_landing_page( $post_id ) ) !== false ) {
		$meta_key = $meta_key . '_' . $template;
	}

	return update_post_meta( $post_id, $meta_key, $meta_value );
}

/**
 * get a list of all landing page templates downloaded from the cloud
 *
 * @return array
 */
function tve_get_downloaded_templates() {
	$options = get_option( 'thrive_tcb_download_lp', array() );

	return ( $options ) ? $options : array();
}


/**
 * loads the landing pages configuration file and returns the item in that array corresponding to the template passed in as parameter
 *
 * @param $template_name
 */
function tve_get_landing_page_config( $template_name ) {
	if ( ! $template_name ) {
		return array();
	}

	if ( tve_is_cloud_template( $template_name ) ) {
		$config = tve_get_cloud_template_config( $template_name, false );

		return $config === false ? array() : $config;
	}

	$config = include plugin_dir_path( dirname( __FILE__ ) ) . 'landing-page/templates/_config.php';

	return isset( $config[ $template_name ] ) ? $config[ $template_name ] : array();
}

/**
 * return a list with the current saved Landing Page templates
 */
function tve_landing_pages_load() {
	$templates = get_option( 'tve_saved_landing_pages_meta', array() );
	$templates = empty( $templates ) ? array() : array_reverse( $templates, true ); // order by date DESC

	return $templates;
}

/**
 * get the link to the google font based on $font
 *
 * @param array|object $font
 */
function tve_custom_font_get_link( $font ) {
	if ( is_array( $font ) ) {
		$font = (object) $font;
	}

	if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font ) ) {
		return Tve_Dash_Font_Import_Manager::getCssFile();
	}

	return '//fonts.googleapis.com/css?family=' . str_replace( ' ', '+', $font->font_name ) . ( $font->font_style ? ':' . $font->font_style : '' ) . ( $font->font_bold ? ',' . $font->font_bold : '' ) . ( $font->font_italic ? $font->font_italic : '' ) . ( $font->font_character_set ? '&subset=' . $font->font_character_set : '' );
}

/**
 * get all fonts created with the font manager
 *
 * @param bool $assoc whether to decode as array or object
 *
 * @return array
 */
function tve_get_all_custom_fonts( $assoc = false ) {
	$all_fonts = get_option( 'thrive_font_manager_options' );
	if ( empty( $all_fonts ) ) {
		$all_fonts = array();
	} else {
		$all_fonts = json_decode( $all_fonts, $assoc );
	}

	return (array) $all_fonts;
}

/**
 *
 * @param $post_id
 * @param $custom_font_classes array containing all the custom font css classes
 */
function tve_update_post_custom_fonts( $post_id, $custom_font_classes ) {
	$all_fonts = tve_get_all_custom_fonts();

	$post_fonts = array();
	foreach ( array_unique( $custom_font_classes ) as $cls ) {
		foreach ( $all_fonts as $font ) {
			if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font->font_name ) ) {
				$post_fonts[] = Tve_Dash_Font_Import_Manager::getCssFile();
			} else if ( $font->font_class == $cls && ! tve_is_safe_font( $font ) ) {
				$post_fonts[] = tve_custom_font_get_link( $font );
				break;
			}
		}
	}

	$post_fonts = array_unique( $post_fonts );

	tve_update_post_meta( $post_id, 'thrive_tcb_post_fonts', $post_fonts );
}

/**
 * get all custom fonts used for a post
 *
 * @param      $post_id
 * @param bool $include_thrive_fonts - whether or not to include Thrive Themes fonts for this post in the list.
 *                                   By default it will return all the fonts that are used in TCB but are not already used from the Theme (admin WP editor)
 *
 * @return array with index => href link
 */
function tve_get_post_custom_fonts( $post_id, $include_thrive_fonts = false ) {
	$post_fonts = tve_get_post_meta( $post_id, 'thrive_tcb_post_fonts' );
	$post_fonts = empty( $post_fonts ) ? array() : $post_fonts;

	if ( empty( $post_fonts ) && ! $include_thrive_fonts ) {
		return array();
	}

	$all_fonts       = tve_get_all_custom_fonts();
	$all_fonts_links = array();
	foreach ( $all_fonts as $f ) {
		if ( Tve_Dash_Font_Import_Manager::isImportedFont( $f->font_name ) ) {
			$all_fonts_links[] = Tve_Dash_Font_Import_Manager::getCssFile();
		} else if ( ! tve_is_safe_font( $f ) ) {
			$all_fonts_links [] = tve_custom_font_get_link( $f );
		}
	}

	if ( empty( $all_fonts ) ) {
		// all fonts have been deleted - delete the saved fonts too for this post
		tve_update_post_meta( $post_id, 'thrive_tcb_post_fonts', array() );
	} else {
		$fixed = array_intersect( $post_fonts, $all_fonts_links );
		if ( count( $fixed ) != count( $post_fonts ) ) {
			$post_fonts = $fixed;
			tve_update_post_meta( $post_id, 'thrive_tcb_post_fonts', $post_fonts );
		}
	}

	$theme_post_fonts = get_post_meta( $post_id, 'thrive_post_fonts', true );
	$theme_post_fonts = empty( $theme_post_fonts ) ? array() : json_decode( $theme_post_fonts, true );

	$post_fonts = empty( $post_fonts ) || ! is_array( $post_fonts ) ? array() : $post_fonts;

	/* return just fonts that will not be loaded from any possible theme shortcodes */

	return $include_thrive_fonts ? array_values( array_unique( array_merge( $post_fonts, $theme_post_fonts ) ) ) : array_diff( $post_fonts, $theme_post_fonts );
}

/**
 * enqueue all the custom fonts used on a post (used only on frontend, not on editor page)
 *
 * @param mixed $post_id              if null -> use the global wp query; if not, load the fonts for that specific post
 * @param bool  $include_thrive_fonts by default thrive themes fonts are included by the theme. for lightboxes for example, we need to include those also
 */
function tve_enqueue_custom_fonts( $post_id = null, $include_thrive_fonts = false ) {
	if ( $post_id === null ) {
		global $wp_query;
		$posts_to_load = $wp_query->posts;
		if ( empty( $posts_to_load ) || ! is_array( $posts_to_load ) ) {
			return;
		}
		$post_id = array();
		foreach ( $posts_to_load as $p ) {
			$post_id [] = $p->ID;
		}
	} else {
		$post_id = array( $post_id );
	}

	foreach ( $post_id as $_id ) {
		tve_enqueue_fonts( tve_get_post_custom_fonts( $_id, $include_thrive_fonts ) );
	}
}

/**
 * Enqueue custom scripts thant need to be loaded on FRONTEND
 */
function tve_enqueue_custom_scripts() {
	global $wp_query;

	$posts_to_load = $wp_query->posts;

	if ( is_array( $posts_to_load ) ) {
		foreach ( $posts_to_load as $post ) {
			tve_check_post_for_scripts_to_enqueue( $post->ID );
		}
	}
}

/**
 * Check post meta if we have to enqueue custom scripts
 *
 * @param $post_id
 */
function tve_check_post_for_scripts_to_enqueue( $post_id ) {
	if ( tve_get_post_meta( $post_id, 'tve_has_masonry' ) ) {
		wp_script_is( 'jquery-masonry' ) || wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );
	}

	/* include wistia script for popover videos */
	if ( tve_get_post_meta( $post_id, 'tve_has_wistia_popover' ) && ! wp_script_is( 'tl-wistia-popover' ) ) {
		wp_enqueue_script( 'tl-wistia-popover', '//fast.wistia.com/assets/external/E-v1.js', array(), '', true );
	}

	$globals = tve_get_post_meta( $post_id, 'tve_globals' );
	if ( ! empty( $globals['js_sdk'] ) ) {
		foreach ( $globals['js_sdk'] as $handle ) {
			wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, tve_social_get_sdk_link( $handle ), array(), false );
		}
	}
}

/**
 * Enqueue the javascript for the social sharing elements, if any is required
 * Will throw an event called "tve_socials_init_[network_name]"
 * It will throw an event for Pinterest by default
 * If the event is thrown the enqueue will be skipped
 *
 * @param $do_action_for array of networks.
 */
function tve_enqueue_social_scripts( $do_action_for = array() ) {
	global $wp_query;

	$posts_to_load = $wp_query->posts;

	if ( ! is_array( $posts_to_load ) ) {
		return;
	}

	foreach ( $posts_to_load as $post ) {
		$globals = tve_get_post_meta( $post->ID, 'tve_globals' );
		if ( ! empty( $globals['js_sdk'] ) ) {
			foreach ( $globals['js_sdk'] as $handle ) {
				$link = tve_social_get_sdk_link( $handle );
				if ( ! $link ) {
					continue;
				}
				wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, $link, array(), false );
			}
		}
	}
}

/**
 * enqueue all fonts passed in as an array with font links
 *
 * @param array $font_array can either be a list of links to google fonts css or a list with font objects returned from the font manager options
 *
 * @return array
 */
function tve_enqueue_fonts( $font_array ) {
	if ( ! is_array( $font_array ) ) {
		return array();
	}
	$return = array();
	/** @var $font object|array|string */
	foreach ( $font_array as $font ) {
		if ( is_string( $font ) ) {
			$href = $font;
		} else if ( is_array( $font ) || is_object( $font ) ) {
			$font_name = is_array( $font ) ? $font['font_name'] : $font->font_name;
			if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font_name ) ) {
				$href = Tve_Dash_Font_Import_Manager::getCssFile();
			} else {
				$href = tve_custom_font_get_link( $font );
			}
		}
		$font_key            = 'tcf_' . md5( $href );
		$return[ $font_key ] = $href;
		wp_enqueue_style( $font_key, $href );
	}

	return $return;
}

/**
 * remove tinymce conflicts
 * 1. if 3rd party products include custom versions of jquery UI, those will completely break the 'wplink' plugin
 * 2. MemberMouse adds some media buttons and does not correctly setup links to images
 */
function tcb_remove_tinymce_conflicts() {
	/* Membermouse adds some extra media buttons */
	remove_all_actions( 'media_buttons_context' );
}

function tve_unique_id( $l = 8 ) {
	return substr( md5( uniqid( mt_rand(), true ) ), 0, $l );
}

/**
 * render the html for the "Custom Menu" widget element
 *
 * called either from the editor section or from frontend, when rendering everything
 *
 * @param array $attributes
 *
 * @return string
 */
function tve_render_widget_menu( $attributes ) {
	$menu_id = ! empty( $attributes['menu_id'] ) ? $attributes['menu_id'] : null;
	if ( $menu_id === 'custom' ) {
		return '';
	}

	$unique_menu_id = isset( $attributes['uuid'] ) ? $attributes['uuid'] : '%1$s';
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && function_exists( 'Nav_Menu_Roles' ) ) {
		/**
		 * If loading the menu via ajax ( in the TCB editor page ) and the Nav Menu Roles plugin is active, we need to add its filtering function here
		 * in order to show the same menu items in the editor page and in Preview
		 */
		$nav_menu_roles = Nav_Menu_Roles();
		if ( ! empty( $nav_menu_roles ) && $nav_menu_roles instanceof Nav_Menu_Roles ) {
			add_filter( 'wp_get_nav_menu_items', array( $nav_menu_roles, 'exclude_menu_items' ) );
		}
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( empty( $items ) ) {
		$placeholder = '';
		if ( $menu_id ) {
			$placeholder = '<div class="thrive-shortcode-html" style="text-align: center">' . __( 'No menu items have been found.', 'thrive-cb' ) . '</div>';
		}

		return $placeholder;
	}
	$attributes['top_level_count'] = count( array_filter( $items, function ( $item ) {
		return empty( $item->menu_item_parent );
	} ) );
	$head_css_attr                 = ! empty( $attributes['head_css'] ) ? sprintf( " data-css='%s'", $attributes['head_css'] ) : '';
	$ul_custom_color               = ! empty( $attributes['ul_attr'] ) ? sprintf( " data-tve-custom-colour='%s'", $attributes['ul_attr'] ) : '';
	$link_custom_color             = ! empty( $attributes['link_attr'] ) ? $attributes['link_attr'] : '';
	$top_link_custom_color         = ! empty( $attributes['top_link_attr'] ) ? $attributes['top_link_attr'] : '';
	$font_family                   = ! empty( $attributes['font_family'] ) ? $attributes['font_family'] : '';

	$GLOBALS['tcb_wp_menu'] = $attributes;

	if ( ! empty( $link_custom_color ) || ! empty( $top_link_custom_color ) ) {
		/* ugly ugly solution */
		$GLOBALS['tve_menu_link_custom_color']     = $link_custom_color;
		$GLOBALS['tve_menu_top_link_custom_color'] = $top_link_custom_color;
		add_filter( 'nav_menu_link_attributes', 'tve_menu_custom_color', 10, 3 );
	}

	if ( ! empty( $font_family ) ) {
		$GLOBALS['tve_menu_top_link_custom_font_family'] = $font_family;
		add_filter( 'nav_menu_link_attributes', 'tve_menu_custom_font_family', 10, 3 );
	}

	$GLOBALS['tve_dropdown_icon'] = ! empty( $attributes['dropdown_icon'] ) ? $attributes['dropdown_icon'] : '';
	add_filter( 'wp_nav_menu_objects', 'tve_menu_filter_objects', 10, 3 );

	if ( ! empty( $attributes['font_class'] ) ) {
		$GLOBALS['tve_menu_font_class'] = $attributes['font_class'];
	}

	$attributes['logo'] = empty( $attributes['logo'] ) ? array() : (array) $attributes['logo'];

	/** @var TCB_Menu_Element $menu_element */
	$menu_element         = tcb_elements()->element_factory( 'menu' );
	$hamburger_trigger    = $menu_element->get_hamburger_trigger_html( $attributes );
	$logo_hamburger_split = ! empty( $attributes['logo'] ) ? TCB_Logo::render_logo( $attributes['logo'] ) : '';

	/* make sure the renderer uses TAr menu walker */
	add_filter( 'wp_nav_menu_args', 'tve_menu_walker' );
	$menu_html = '<div class="thrive-shortcode-html thrive-shortcode-html-editable tve_clearfix" ' . $head_css_attr . '>' .
	             $hamburger_trigger .
	             '<div class="tcb-hamburger-logo">' . $logo_hamburger_split . '</div>' .
	             wp_nav_menu( array(
		             'echo'           => false,
		             'menu'           => $menu_id,
		             'container'      => false,
		             'theme_location' => 'primary',
		             'items_wrap'     => '<ul' . $ul_custom_color . ' id="' . $unique_menu_id . '" class="%2$s"' . ( ! empty( $attributes['font_size'] ) ? ' style="font-size:' . $attributes['font_size'] . '"' : '' ) . '>%3$s</ul>',
		             'menu_class'     => 'tve_w_menu ' . $attributes['dir'] . ' ' . ( ! empty( $attributes['font_class'] ) ? $attributes['font_class'] . ' ' : '' ) . ( ! empty( $attributes['color'] ) ? $attributes['color'] : '' ),
	             ) ) . '</div>';
	remove_filter( 'wp_nav_menu_args', 'tve_menu_walker' );

	/* clear out the global variable */
	unset( $GLOBALS['tve_menu_link_custom_color'], $GLOBALS['tve_menu_top_link_custom_color'], $GLOBALS['tve_menu_font_class'], $GLOBALS['tve_menu_top_link_custom_font_family'], $GLOBALS['tve_menu_group_edit'], $GLOBALS['tcb_wp_menu'] );
	remove_filter( 'nav_menu_link_attributes', 'tve_menu_custom_color' );
	remove_filter( 'nav_menu_link_attributes', 'tve_menu_custom_font_family' );
	remove_filter( 'wp_nav_menu_objects', 'tve_menu_filter_objects' );

	/* parse events on the generated html */
	if ( ! is_editor_page_raw( true ) ) {
		tve_parse_events( $menu_html );
	}

	return $menu_html;
}

/**
 * Always use the custom menu walker for TAr WP Menus
 *
 * @param array $args
 *
 * @return mixed
 */
function tve_menu_walker( $args ) {
	$args['walker'] = new TCB_Menu_Walker();

	return $args;
}

/**
 * Whether or not the current environment should use positional selectors for CM
 * This is used on the lp-build site
 *
 * @return bool|mixed
 */
function tcb_custom_menu_positional_selectors() {
	/**
	 * Filter. Allows using positional styling for the top level of the custom menu
	 * This is implemented on the template builder site (returns true)
	 *
	 * @param bool $value whether or not the current install uses positional selectors
	 *
	 * @return bool
	 */
	return apply_filters( 'tcb_custom_menu_positional', false );
}

/**
 * Filter menu items before rendering
 *
 * @param array $items
 *
 * @return mixed
 */
function tve_menu_filter_objects( $items ) {
	$uses_positional_selectors = tcb_custom_menu_positional_selectors();
	$top_level_count           = 0;
	$last_top_level            = null;
	$icons                     = tve_menu_custom_create_dropdown_icons( $GLOBALS['tve_dropdown_icon'] );
	foreach ( $items as $menu_item ) {
		$dropdown = '';
		if ( in_array( 'menu-item-has-children', $menu_item->classes ) ) {
			/* wtf is with this class name? */
			$dropdown = '<span class="tve-item-dropdown-trigger">' . $icons . '</span>';
		}
		/* wtf is with this class name? */
		$menu_item->title = '<span class="tve-disabled-text-inner">' . $menu_item->title . '</span>' . $dropdown;

		/* solves CSS positional selectors for lp-build */
		if ( $uses_positional_selectors && isset( $menu_item->menu_item_parent ) && (int) $menu_item->menu_item_parent === 0 ) {
			$top_level_count ++;
			$menu_item->_tcb_pos_selector = $top_level_count === 1 ? ':first-child' : ":nth-child({$top_level_count})";
			$last_top_level               = $menu_item;
		}
	}

	if ( $uses_positional_selectors && isset( $last_top_level ) ) {
		$last_top_level->_tcb_pos_selector = ':last-child';
	}

	return $items;
}

function tve_menu_custom_create_dropdown_icons( $style ) {
	if ( empty( $style ) ) {
		return '';
	}
	$icon_styles = tcb_elements()->element_factory( 'menu' )->get_icon_styles();

	return '<svg xmlns="http://www.w3.org/2000/svg" class="tve-dropdown-icon-up" viewBox="' . $icon_styles[ $style ]['box'] . '">' . $icon_styles[ $style ]['up'] . '</svg>';
}

/**
 * append custom color attributes to the link items from the menu
 *
 * @param $attrs
 *
 * @return mixed
 */
function tve_menu_custom_color( $attrs, $menu_item ) {
	$custom_color = $menu_item->menu_item_parent ? 'tve_menu_link_custom_color' : 'tve_menu_top_link_custom_color';
	$value        = isset( $GLOBALS[ $custom_color ] ) ? $GLOBALS[ $custom_color ] : '';

	if ( ! $value ) {
		return $attrs;
	}
	$attrs['data-tve-custom-colour'] = $value;

	return $attrs;
}

function tve_menu_custom_font_family( $attrs, $menu_item ) {
	$font_family = $GLOBALS['tve_menu_top_link_custom_font_family'];
	$style       = 'font-family: ' . $font_family . ';';

	if ( isset( $attrs['style'] ) && ! empty( $attrs['style'] ) ) {
		$style = trim( ';', $attrs['style'] ) . ';' . $style;
	}

	$attrs['style'] = $style;

	return $attrs;
}

/**
 * custom call of an action hook - this will forward the call to the WP do_action function
 * it will inject parameters read from $_GET based on the filter that others might use
 *
 * @param string $hook  required. The action hook to be called
 * @param mixed  $_args arguments that will be passed on to the do_action call
 */
function tve_do_action() {
	/**
	 * filter to allow passing variables from $_GET into the various actions
	 * this is used only on editor page
	 */
	$_get_fields = apply_filters( 'tcb_required_get_fields', array() );
	$args        = func_get_args();

	if ( ! is_array( $_get_fields ) ) {
		$_get_fields = array();
	}

	foreach ( $_get_fields as $field ) {
		$args [] = isset( $_GET[ $field ] ) ? $_GET[ $field ] : null;
	}

	return call_user_func_array( 'do_action', $args );
}

/**
 * sort the user-defined templates alphabetically by name
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function tve_tpl_sort( $a, $b ) {
	return strcasecmp( $a['name'], $b['name'] );
}

/**
 *
 * transform any url into a protocol-independent url
 *
 * @param string $raw_url
 *
 * @return string
 */
function tve_url_no_protocol( $raw_url ) {
	return preg_replace( '#http(s)?://#', '//', $raw_url );
}

/**
 * called via AJAX, it will load a file from a list of allowed files from the editor
 * designed to work
 */
function tve_ajax_load() {

	if ( ob_get_contents() ) {
		ob_clean();
	}
	if ( empty( $_POST['ajax_load'] ) ) {
		return;
	}
	$file = $_POST['ajax_load'];

	switch ( $file ) {
		case 'control_panel':
		case 'lb_icon':
		case 'lb_lead_generation_code':
		case 'lb_post_grid':
		case 'lb_revision_manager':
		case 'lb_social':
		case 'lb_custom_css':
		case 'lb_custom_html':
		case 'lb_full_html':
		case 'lb_global_scripts':
		case 'lb_google_map':
		case 'lb_image_link':
		case 'lb_landing_pages':
		case 'lb_table':
		case 'lb_text_link':
		case 'lb_text_link_settings':
		case 'lb_ultimatum_shortcode':
			include plugin_dir_path( dirname( __FILE__ ) ) . 'editor/' . $file . '.php';
			break;
		case 'sc_thrive_custom_menu':
		case 'sc_thrive_custom_phone':
		case 'sc_thrive_leads_shortcode':
		case 'sc_thrive_ultimatum_shortcode':
		case 'sc_thrive_optin':
		case 'sc_thrive_posts_list':
		case 'sc_widget_menu':
		case 'sc_icon':
			include plugin_dir_path( dirname( __FILE__ ) ) . 'shortcodes/templates/' . $file . '.php';
			break;
		default:
			do_action( 'tcb_ajax_load', $file );
			break;
	}

	exit();
}

/**
 * Fields that will be displayed with differences in revisions page(admin section)
 *
 * @param $fields
 *
 * @return mixed
 */
function tve_post_revision_fields( $fields ) {
	$fields['tve_revision_tve_updated_post']    = __( 'Thrive Architect Content', 'thrive-cb' );
	$fields['tve_revision_tve_user_custom_css'] = __( 'Thrive Architect Custom CSS', 'thrive-cb' );
	$fields['tve_revision_tve_landing_page']    = __( 'Landing Page', 'thrive-cb' );

	return $fields;
}

/**
 * At this moment post is reverted to required revision.
 * This means the post is saved and a new revision is already created.
 * When a revision is created all metas are assigned to revision;
 *
 * @param $post_id
 * @param $revision_id
 *
 * @return bool
 * @see tve_save_post_callback
 *
 * Get all the metas of the revision received as parameter and set it for the newly revision created.
 * Set all revision metas to post received as parameter
 *
 */
function tve_restore_post_to_revision( $post_id, $revision_id ) {
	$revisions     = wp_get_post_revisions( $post_id );
	$last_revision = array_shift( $revisions );

	if ( ! $last_revision ) {
		return false;
	}

	$meta_keys = tve_get_used_meta_keys();
	foreach ( $meta_keys as $meta_key ) {
		$revision_content = get_metadata( 'post', $revision_id, 'tve_revision_' . $meta_key, true );
		update_metadata( 'post', $last_revision->ID, 'tve_revision_' . $meta_key, $revision_content );

		if ( $meta_key === 'tve_landing_page' ) {
			update_post_meta( $post_id, $meta_key, $revision_content );
		} else {
			tve_update_post_meta( $post_id, $meta_key, $revision_content );
		}
	}
}

/**
 * Filter called from wp_save_post_revision. If this logic returns true a post revision will be added by WP
 * If there are any changes in meta then we need a revision to be made
 *
 * @param $post_has_changed
 * @param $last_revision
 * @param $post
 *
 * @return bool
 * @see wp_save_post_revision
 *
 */
function tve_post_has_changed( $post_has_changed, $last_revision, $post ) {
	$meta_keys = tve_get_used_meta_keys();

	/**
	 * check the meta
	 * if there is any meta differences a revision should be made
	 */
	foreach ( $meta_keys as $meta_key ) {
		if ( $meta_key === 'tve_landing_page' ) {
			$post_content = get_post_meta( $post->ID, $meta_key, true );
		} else {
			$post_content = tve_get_post_meta( $post->ID, $meta_key );
		}
		$revision_content = get_post_meta( $last_revision->ID, 'tve_revision_' . $meta_key, true );
		$post_has_changed = $revision_content !== $post_content;
		if ( $post_has_changed ) {
			return true;
		}
	}

	/** @var $total_fields array fields that are tracked for versioning */
	$total_fields = array_keys( _wp_post_revision_fields() );

	/** @var $tve_custom_fields array fields that are pushed to be tracked by this plugin */
	$tve_custom_fields = array_keys( tve_post_revision_fields( array() ) );

	/** @var $to_be_checked array remove additional plugin tracking fields */
	$to_be_checked = array();
	foreach ( $total_fields as $total ) {
		if ( in_array( $total, $tve_custom_fields ) ) {
			continue;
		}
		$to_be_checked[] = $total;
	}

	foreach ( $to_be_checked as $field ) {
		if ( normalize_whitespace( $post->$field ) != normalize_whitespace( $last_revision->$field ) ) {
			$post_has_changed = true;
			break;
		}
	}

	return $post_has_changed;
}

/**
 * Return an array with meta keys that are used for custom content on posts
 *
 * @return array
 * @see tve_save_post_callback, tve_post_has_changed, tve_restore_post_to_revision
 *
 */
function tve_get_used_meta_keys() {
	$meta_keys = array(
		'tve_landing_page',
		'tve_disable_theme_dependency',
		'tve_content_before_more',
		'tve_content_more_found',
		'tve_save_post',
		'tve_custom_css',
		'tve_user_custom_css',
		'tve_page_events',
		'tve_globals',
		'tve_global_scripts',
		'thrive_icon_pack',
		'thrive_tcb_post_fonts',
		'tve_has_masonry',
		'tve_has_typefocus',
		'tve_updated_post',
		'tve_has_wistia_popover',
	);

	return $meta_keys;
}

/**
 * Called when post is loaded and tve_revert_theme exists in get request
 * Redirects the user to post edit form
 */
function tve_revert_page_to_theme() {
	if ( ! isset( $_GET['tve_revert_theme'] ) ) {
		return;
	}
	if ( ! isset( $_GET['post'] ) || ! isset( $_GET['action'] ) ) {
		return;
	}
	$post_id = $_GET['post'];

	if ( tve_post_is_landing_page( $_GET['post'] ) ) {
		delete_post_meta( $post_id, 'tve_landing_page' );
		//Delete Also The Setting To Disable Theme CSS
		delete_post_meta( $post_id, 'tve_disable_theme_dependency' );
		//force save, a revision needs to be created
		wp_update_post( array(
			'ID'                => $post_id,
			'post_modified'     => current_time( 'mysql' ),
			'post_modified_gmt' => current_time( 'mysql' ),
			'post_title'        => get_the_title( $post_id ),
		) );
		wp_redirect( get_edit_post_link( $post_id, 'url' ) );
		exit();
	}
}

/**
 * strip out any un-necessary stuff from the content before displaying it on frontend
 *
 * @param string $tve_saved_content
 *
 * @return string the clean content
 */
function tcb_clean_frontend_content( $tve_saved_content ) {

	$patterns = array(

		/**
		 * strip out the lead generation code
		 */
		'/__CONFIG_lead_generation_code__(.+?)__CONFIG_lead_generation_code__/s',

		/**
		 * Strip out Dynamic Group Editing Configuration Code
		 */
		'/__CONFIG_group_edit__(.+?)__CONFIG_group_edit__/s',

		/**
		 * Strip the Dynamic Palette Configuration Code
		 */
		'#__CONFIG_colors_palette__(.+?)__CONFIG_colors_palette__#',

		/**
		 * Strip out Local Colors Configuration Code
		 */
		'/__CONFIG_local_colors__(.+?)__CONFIG_local_colors__/s',
	);

	$tve_saved_content = preg_replace( $patterns, '', $tve_saved_content );

	return $tve_saved_content;
}

/**
 * create a hidden input containing the error messages instead of holding them in the html content
 *
 * @param array $match
 *
 * @return string
 */
function tcb_lg_err_inputs( $match ) {
	return '<input type="hidden" class="tve-lg-err-msg" value="' . htmlspecialchars( $match[1] ) . '">';
}

/**
 * One place to rule them all
 * Please use this function to read the FB AppID used in Social Sharing Element
 *
 * @return string
 */
function tve_get_social_fb_app_id() {
	return get_option( 'tve_social_fb_app_id', '' );
}

/**
 * Please use this function to read the Disqus Short Name used in Disqus Comments Element
 *
 * @return string
 */
function tve_get_comments_disqus_shortname() {
	return get_option( 'tve_comments_disqus_shortname', '' );
}

/**
 * Please use this function to read the Facebook Admins used in Facebook Comments Element
 *
 * @return array
 */
function tve_get_comments_facebook_admins() {
	return get_option( 'tve_comments_facebook_admins', '' );
}

/**
 * Set the path where the translation files are being kept
 */
function tve_load_plugin_textdomain() {
	$domain = 'thrive-cb';
	$locale = $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	$path = 'thrive-visual-editor/languages/';
	$path = apply_filters( 'tve_filter_plugin_languages_path', $path );

	load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, false, $path );
}

/**
 * Check the Object font sent as param if it's web sef font
 *
 * @param $font array|StdClass
 *
 * @return bool
 */
function tve_is_safe_font( $font ) {
	foreach ( tve_dash_font_manager_get_safe_fonts() as $safe_font ) {
		if ( ( is_object( $font ) && $safe_font['family'] === $font->font_name )
		     || ( is_array( $font ) && $safe_font['family'] === $font['font_name'] )
		) {
			return true;
		}
	}

	return false;
}

/**
 * Remove the web safe fonts from the list cos we don't want them to import them from google
 * They already exists loaded in browser from user's computer
 *
 * @param $fonts_saved
 *
 * @return mixed
 */
function tve_filter_custom_fonts_for_enqueue_in_editor( $fonts_saved ) {
	$safe_fonts = tve_dash_font_manager_get_safe_fonts();
	foreach ( $safe_fonts as $safe ) {
		foreach ( $fonts_saved as $key => $font ) {
			if ( is_object( $font ) && $safe['family'] === $font->font_name ) {
				unset( $fonts_saved[ $key ] );
			} elseif ( is_array( $font ) && $safe['family'] === $font['font_name'] ) {
				unset( $fonts_saved[ $key ] );
			}
		}
	}

	return $fonts_saved;
}

/**
 * includes a message in the media uploader window about the allowed file types
 */
function tve_media_restrict_filetypes() {
	$file_types = array(
		'zip',
		'jpg',
		'gif',
		'png',
		'pdf',
	);
	foreach ( $file_types as $file_type ) {
		echo '<p class="tve-media-message tve-media-allowed-' . $file_type . '" style="display: none"><strong>' . sprintf( __( 'Only %s files are accepted' ), '.' . $file_type ) . '</strong></p>';
	}
}

function tve_json_utf8_slashit( $value ) {
	return str_replace( array( '_tveutf8_', '_tve_quote_' ), array( '\u', '\"' ), $value );
}

function tve_json_utf8_unslashit( $value ) {
	return str_replace( array( '\u', '\"' ), array( '_tveutf8_', '_tve_quote_' ), $value );
}

/**
 * Loads dashboard's version file
 */
function tve_load_dash_version() {
	$tve_dash_path      = dirname( dirname( __FILE__ ) ) . '/thrive-dashboard';
	$tve_dash_file_path = $tve_dash_path . '/version.php';

	if ( is_file( $tve_dash_file_path ) ) {
		$version                                  = require_once( $tve_dash_file_path );
		$GLOBALS['tve_dash_versions'][ $version ] = array(
			'path'   => $tve_dash_path . '/thrive-dashboard.php',
			'folder' => '/thrive-visual-editor',
			'from'   => 'plugins',
		);
	}
}

function tve_custom_form_submit() {

	$post = $_POST;
	/**
	 * action filter -  allows hooking into the form submission event
	 *
	 * @param array $post the full _POST data
	 *
	 */
	do_action( 'tcb_api_form_submit', $post );
}

/**
 * AJAX call on a Lead Generation form that's connected to an api
 *
 * @param bool $output whether to output the result directly or return it
 *
 * @return mixed
 */
function tve_api_form_submit( $output = true ) {

	if ( ! is_bool( $output ) ) {
		/**
		 * tve_api_form_submit is also called from ajax via wp_ajax_tve_api_form_submit or wp_ajax_nopriv_tve_api_form_submit actions
		 *
		 * When this is the case, the $output parameter is an empty string
		 */
		$output = true;
	}

	/* make sure these are not sent via request */
	unset( $_POST['$$trusted'], $_REQUEST['$$trusted'], $_GET['$$trusted'] );
	$data = tve_sanitize_data_recursive( $_POST, 'sanitize_textarea_field' );

	if ( empty( $data['tcb_token'] ) ) {
		/* this field is always needed. If not sent, the current request does not come from a web browser */
		wp_die( '' );
	}

	if ( ! empty( $data['_tcb_id'] ) ) { // form settings id
		$settings = \TCB\inc\helpers\FormSettings::get_one( $data['_tcb_id'] );
		if ( ! $settings->ID ) {
			return TCB_Utils::maybe_send_json( array(
				'error' => __( 'Something went wrong! Please contact site owner', 'thrive-cb' ),
			), $output );
		}
		/**
		 * populate data with settings from database
		 */
		$settings->populate_request( $data );
	}

	if ( ! empty( $data['_use_captcha'] ) ) {
		$captcha_url = 'https://www.google.com/recaptcha/api/siteverify';
		$captcha_api = Thrive_Dash_List_Manager::credentials( 'recaptcha' );

		$_capthca_params = array(
			'response' => $data['g-recaptcha-response'],
			'secret'   => empty( $captcha_api['secret_key'] ) ? '' : $captcha_api['secret_key'],
			'remoteip' => $_SERVER['REMOTE_ADDR'],
		);

		$request  = tve_dash_api_remote_post( $captcha_url, array( 'body' => $_capthca_params ) );
		$response = json_decode( wp_remote_retrieve_body( $request ) );

		if ( empty( $response ) || $response->success === false || ( ! empty( $captcha_api['connection'] ) && $captcha_api['connection']['version'] === 'v3' && $response->score <= $captcha_api['connection']['threshold'] ) ) {
			return TCB_Utils::maybe_send_json( array(
				'field' => 'captcha',
				'error' => __( 'We are detecting suspicious activity from your device. Please try in another browser or contact the website administrator.', 'thrive-cb' ),
			), $output );
		}
	}

	if ( empty( $data['email'] ) ) {
		return TCB_Utils::maybe_send_json( array(
			'field' => 'email',
			'error' => __( 'The email address is required', 'thrive-cb' ),
		), $output );
	}

	if ( ! is_email( $data['email'] ) ) {
		return TCB_Utils::maybe_send_json( array(
			'field' => 'email',
			'error' => __( 'The email address is invalid', 'thrive-cb' ),
		), $output );
	}

	/**
	 * if a file field exists and is required, validate the fact that file IDs have been sent
	 * Also checks nonces and discards the request if those are not valid
	 */
	if ( ! empty( $data['tcb_file_id'] ) ) {
		$file_valid = FileUploadConfig::get_one( $data['tcb_file_id'] )->validate_form_submit( $data );
		if ( $file_valid !== true ) {
			return TCB_Utils::maybe_send_json( array(
				'field' => 'file',
				'error' => __( $file_valid, 'thrive-cb' ),
			), $output );
		}
	}

	$consent_config = array();

	/**
	 * bugfix for empty consent_config => this means that the consent is required and enabled
	 */
	if ( isset( $data['consent_config'] ) && $data['consent_config'] === '' ) {
		$consent_config = array(
			'enabled'     => 1,
			'required'    => 1,
			'always_send' => array(),
		);
	} elseif ( ! empty( $data['consent_config'] ) ) {
		$consent_config = Thrive_Dash_List_Manager::decodeConnectionString( $data['consent_config'] );
		/**
		 * if consent_config is disabled, empty it here
		 */
		if ( empty( $consent_config['enabled'] ) ) {
			$consent_config = array();
		}
	}
	$data['consent_config'] = $consent_config;
	/* make sure always_send key exists */
	if ( ! empty( $data['consent_config']['enabled'] ) && ( ! isset( $data['consent_config']['always_send'] ) || ! is_array( $data['consent_config']['always_send'] ) ) ) {
		$data['consent_config']['always_send'] = array();
	}

	/**
	 * Validate user consent
	 */
	if ( ! empty( $consent_config['required'] ) && empty( $data['user_consent'] ) ) {
		return TCB_Utils::maybe_send_json( array(
			'field' => 'consent',
			'error' => __( 'User consent is required', 'thrive-cb' ),
		), $output );
	}


	$post = $data;
	unset( $post['action'], $post['__tcb_lg_fc'], $post['_back_url'] );

	/**
	 * action filter -  allows hooking into the form submission event
	 *
	 * @param array $post the full _POST data
	 *
	 */
	do_action( 'tcb_api_form_submit', $post );

	if ( isset( $settings ) ) {
		$connections = $settings->apis;
	} elseif ( ! empty( $data['__tcb_lg_fc'] ) ) {
		$connections = Thrive_Dash_List_Manager::decodeConnectionString( $data['__tcb_lg_fc'] ); // previous version
	}

	if ( empty( $connections ) ) {
		return TCB_Utils::maybe_send_json( array(
			'error' => __( 'No connection for this form', 'thrive-cb' ),
		), $output );
	}

	//these are not needed anymore
	unset( $data['__tcb_lg_fc'], $data['_back_url'], $data['action'] );

	$result        = array();
	$data['name']  = ! empty( $data['name'] ) ? $data['name'] : '';
	$data['phone'] = ! empty( $data['phone'] ) ? $data['phone'] : '';

	/**
	 * filter - allows modifying the data before submitting it to the API
	 *
	 * @param array $data
	 */
	$data = apply_filters( 'tcb_api_subscribe_data', $data );

	if ( isset( $data['__tcb_lg_msg'] ) ) {
		$result['form_messages'] = Thrive_Dash_List_Manager::decodeConnectionString( $data['__tcb_lg_msg'] );
	}

	$available = Thrive_Dash_List_Manager::getAvailableAPIs( true );

	/**
	 * Filter the api connections before sending form data
	 *
	 * @param array $connections APIs that will receive subscription data
	 * @param array $available   all available API connections (list of all API connections setup from Thrive Dashboard)
	 * @param array $data        POST data to send to the api connection instance
	 *
	 * @return array
	 */
	$connections = apply_filters( 'tcb_api_subscribe_connections', $connections, $available, $data );

	foreach ( $available as $key => $connection ) {

		if ( false === array_key_exists( $key, $connections ) ) {
			continue;
		}

		/**
		 * Check if user gave consent for the specified services
		 */
		if ( ! empty( $consent_config['enabled'] ) && ! in_array( $key, $consent_config['always_send'] ) ) {
			/* only send to API if user gave consent */
			if ( empty( $data['user_consent'] ) ) {
				continue;
			}
		}

		if ( $key == 'klicktipp' && $data['_submit_option'] == 'klicktipp-redirect' ) {
			$result['redirect'] = tve_api_add_subscriber( $connection, $connections[ $key ], $data );
			if ( filter_var( $result['redirect'], FILTER_VALIDATE_URL ) !== false ) {
				$result[ $key ] = true;
			}
		} else {
			// Not sure how we can perform validations / mark errors here
			$result[ $key ] = tve_api_add_subscriber( $connection, $connections[ $key ], $data );
		}
	}

	/**
	 * $result will contain boolean 'true' or string error messages for each connected api
	 * these error messages will literally have no meaning for the user - we'll just store them in a db table and show them in admin somewhere
	 */
	return TCB_Utils::maybe_send_json( $result, $output );
}

/**
 * make an api call to a subscribe a user
 *
 * @param string|Thrive_Dash_List_Connection_Abstract $connection
 * @param mixed                                       $list_identifier the list identifier
 * @param array                                       $data            submitted data
 * @param bool                                        $log_error       whether or not to log errors in a DB table
 *
 * @return result mixed
 */
function tve_api_add_subscriber( $connection, $list_identifier, $data, $log_error = true ) {

	if ( is_string( $connection ) ) {
		$connection = Thrive_Dash_List_Manager::connectionInstance( $connection );
	}

	$key = $connection->getKey();

	/**
	 * filter - allows modifying the sent data to each individual API instance
	 *
	 * @param array                           $data            data to be sent to the API instance
	 * @param Thrive_List_Connection_Abstract $connection      the connection instance
	 * @param mixed                           $list_identifier identifier for the list which will receive the new email
	 */
	$data = apply_filters( 'tcb_api_subscribe_data_instance', $data, $connection, $list_identifier );

	/** @var Thrive_Dash_List_Connection_Abstract $connection */
	$result = $connection->addSubscriber( $list_identifier, $data );

	if ( ! $log_error || true === $result || ( $key === 'klicktipp' && filter_var( $result, FILTER_VALIDATE_URL ) !== false ) ) {
		/**
		 * Fires when a new Lead is Created from a Thrive Leads Form
		 *
		 * @param array Lead Data
		 * @param null|array User Data
		 *
		 * @api
		 */
		do_action( 'thrive_core_lead_signup', tve_get_lead_gen_form_data( $data ), tvd_get_current_user_details() );

		return $result;
	}

	global $wpdb;

	/**
	 * Support also array error messages
	 */
	$db_error = $result;
	if ( is_array( $db_error ) ) {
		if ( ! empty( $db_error['error'] ) ) {
			$db_error = $db_error['error'];
		} elseif ( ! empty( $db_error['message'] ) ) {
			$db_error = $db_error['message'];
		} else {
			$db_error = json_encode( $db_error ); //default to json-encode, as this is an unknown error format
		}
	}
	/**
	 * at this point, we need to log the error in a DB table, so that the user can see all these error later on and (maybe) re-subscribe the user
	 */
	$log_data = array(
		'date'          => date( 'Y-m-d H:i:s' ),
		'error_message' => tve_sanitize_data_recursive( $db_error, 'sanitize_text_field' ),
		'api_data'      => serialize( tve_sanitize_data_recursive( $data, 'sanitize_text_field' ) ),
		'connection'    => $connection->getKey(),
		'list_id'       => maybe_serialize( tve_sanitize_data_recursive( $list_identifier, 'sanitize_text_field' ) ),
	);

	$wpdb->insert( $wpdb->prefix . 'tcb_api_error_log', $log_data );

	return $result;
}

/**
 * Retrieves the Lead Generation form data
 *
 * @param array $data
 *
 * @return array[]
 */
function tve_get_lead_gen_form_data( $data = array() ) {

	$lead_data = array(
		'form_data' => array(),
	);

	/**
	 * Allow other plugins that inject data into Lead Generation forms to add data here
	 *
	 * @parm $data array
	 */
	$data = apply_filters( 'tcb_parse_lead_gen_form_data', $data );

	$banned_lead_gen_keys = array( '_submit_option', '_sendParams', '_api_custom_fields', 'tve_mapping', 'tve_labels', 'consent_config', '__tcb_lg_msg', 'external_plugin_fields' );

	foreach ( $data as $key => $value ) {

		if ( in_array( $key, $banned_lead_gen_keys, true ) ) {
			continue;
		}

		$lead_data['form_data'][ $key ] = $value;
	}

	/**
	 * External plugin fields comes from the tcb_parse_lead_gen_form_data and it is used to parse external fields that are from other thrive plugins to the hook
	 */
	if ( ! empty( $data['external_plugin_fields'] ) ) {
		$lead_data = array_merge( $lead_data, $data['external_plugin_fields'] );
	}

	return $lead_data;
}

/**
 * called on the 'init' hook
 *
 * load all classes and files needed for TCB
 */
function tve_load_tcb_classes() {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'landing-page/inc/TCB_Landing_Page_Transfer.php';

	\Tcb\Integrations\WooCommerce\Main::init();
}

/**
 * @return TCB_Editor
 */
function tcb_editor() {
	return TCB_Editor::instance();
}

/**
 * Get the global cpanel configuration attributes (position, side, minimized etc)
 *
 * @return array
 */
function tve_cpanel_attributes() {
	$defaults = array(
		'position' => 'left',
	);

	$user_option = get_user_option( 'tve_cpanel_config' );
	if ( ! is_array( $user_option ) ) {
		$user_option = array();
	}

	$user_option = array_merge( $defaults, $user_option );

	return $user_option;
}

/**
 * Get the post categories
 *
 * @return array
 */
function tve_get_post_categories() {
	$categories = array( 0 => __( 'All categories', 'thrive-cb' ) );
	foreach ( get_categories() as $cat ) {
		$categories[ $cat->cat_ID ] = $cat->cat_name;
	}

	return $categories;
}

/**
 * Get all defined menus
 *
 * @return array
 */
function tve_get_custom_menus() {
	$menu_items = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
	$all_menus  = array();
	foreach ( $menu_items as $menu ) {
		$all_menus[] = array(
			'id'   => $menu->term_id,
			'name' => $menu->name,
		);
	}

	return $all_menus;
}

/**
 * include a template file from inc/views folder
 *
 * @param string $file
 * @param mixed  $data
 * @param bool   $return    whether or not to return the content instead of outputting it
 * @param string $namespace namespace to use when locating the file
 *
 * @return string|null $content string when $return is non-false and void otherwise
 */
function tcb_template( $file, $data = null, $return = false, $namespace = 'views' ) {
	if ( strpos( $file, '.php' ) === false && strpos( $file, '.phtml' ) === false ) {
		$file .= '.php';
	}

	switch ( $namespace ) {
		case 'backbone':
			$folder = 'inc/backbone/';
			break;
		case 'views':
		default:
			$folder = 'inc/views/';
			break;
	}

	$file      = ltrim( $file, '\\/' );
	$file_path = apply_filters( 'tcb.template_path', TVE_TCB_ROOT_PATH . $folder . $file, $file, $data, $namespace );
	$content   = null;

	if ( ! is_file( $file_path ) ) {
		return false;
	}

	if ( false !== $return ) {
		ob_start();
		include $file_path;
		$content = ob_get_contents();
		ob_end_clean();
	} else {
		include $file_path;
	}

	return $content;
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
function tcb_icon( $icon, $return = false, $namespace = 'sidebar', $extra_class = '', $svg_attr = array() ) {
	$use = $namespace !== 'sidebar' ? 'tcb-icon-' : 'icon-';

	$extra_attr = '';
	if ( ! empty( $svg_attr ) ) {
		foreach ( $svg_attr as $attr_name => $attr_value ) {
			$extra_attr .= ( $extra_attr ? ' ' : '' ) . $attr_name . '="' . esc_attr( $attr_value ) . '"';
		}
	}

	$html = '<svg class="tcb-icon tcb-icon-' . $icon . ( empty( $extra_class ) ? '' : ' ' . $extra_class ) . '"' . $extra_attr . '><use xlink:href="#' . $use . $icon . '"></use></svg>';

	if ( false !== $return ) {
		return $html;
	}

	echo $html;
}

/**
 * Gets the post revisions as an array of objects
 *
 * @param null $post
 *
 * @return array
 */
function tve_get_post_revisions( $post = null ) {

	$post_id = ( $post instanceof WP_Post ) ? $post->ID : intval( $post );

	$revisions = wp_get_post_revisions( $post_id );
	$return    = array();

	foreach ( $revisions as $revision ) {
		$modified                          = strtotime( $revision->post_modified );
		$modified_gmt                      = strtotime( $revision->post_modified_gmt );
		$now_gmt                           = time();
		$restore_link                      = str_replace( '&amp;', '&', wp_nonce_url(
			add_query_arg(
				array(
					'revision' => $revision->ID,
					'action'   => 'restore',
				),
				admin_url( 'revision.php' )
			),
			"restore-post_{$revision->ID}"
		) );
		$show_avatars                      = get_option( 'show_avatars' );
		$authors[ $revision->post_author ] = array(
			'id'     => (int) $revision->post_author,
			'avatar' => $show_avatars ? get_avatar( $revision->post_author, 64 ) : '',
			'name'   => get_the_author_meta( 'display_name', $revision->post_author ),
		);
		$autosave                          = (bool) wp_is_post_autosave( $revision );
		$return[]                          = array(
			'id'         => $revision->ID,
			'title'      => get_the_title( $post_id ),
			'author'     => $authors[ $revision->post_author ],
			'date'       => date_i18n( __( 'M j, Y @ G:i' ), $modified ),
			'dateShort'  => date_i18n( _x( 'j M Y,G:i', 'revision date short format' ), $modified ),
			'timeAgo'    => sprintf( __( '%s ago', 'thrive-cb' ), human_time_diff( $modified_gmt, $now_gmt ) ),
			'autosave'   => $autosave,
			'restoreUrl' => $restore_link,
		);
	}

	return $return;

}

/**
 * Computes the time settings necessary for Countdown Element and Countdown Evergreen Element
 */
function tve_get_time_settings() {

	$timezone_offset = get_option( 'gmt_offset' );
	$sign            = ( $timezone_offset < 0 ? '-' : '+' );
	$min             = abs( $timezone_offset ) * 60;
	$hour            = floor( $min / 60 );
	$tzd             = $sign . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min % 60, 2, '0', STR_PAD_LEFT );

	return array(
		'timezone_offset' => $timezone_offset,
		'sign'            => $sign,
		'min'             => $min,
		'hour'            => $hour,
		'tzd'             => $tzd,
	);
}

/**
 * Add Architect ajax nonce to the after auth data so we can refresh it
 *
 * @param $data
 *
 * @return mixed
 */
function tcb_auth_check_data( $data ) {
	$data ['tcb_nonce'] = wp_create_nonce( TCB_Editor_Ajax::NONCE_KEY );

	return $data;
}

/**
 * Filters the upload user template location.
 * Callback used in action_save_user_template function
 *
 * @param $upload
 *
 * @return mixed
 */
function tve_filter_upload_user_template_location( $upload ) {
	$sub_dir = '/thrive-visual-editor/user_templates';

	$upload['path']   = $upload['basedir'] . $sub_dir;
	$upload['url']    = $upload['baseurl'] . $sub_dir;
	$upload['subdir'] = $sub_dir;

	return $upload;
}

if ( ! function_exists( 'tve_is_numeric_array' ) ) {
	/**
	 * Determines if the variable is a numeric-indexed array.
	 *
	 * @param mixed $data Variable to check.
	 *
	 * @return bool Whether the variable is a list.
	 * @since 4.4.0
	 *
	 */
	function tve_is_numeric_array( $data ) {
		if ( ! is_array( $data ) ) {
			return false;
		}

		$keys        = array_keys( $data );
		$string_keys = array_filter( $keys, 'is_string' );

		return count( $string_keys ) === 0;
	}
}

/**
 * Own implementation for array_replace_recursive so we can overwrite numeric arrays
 *
 * @return mixed
 */
function tve_array_replace_recursive() {

	if ( ! function_exists( 'tve_array_recurse' ) ) {
		/**
		 * Merge two arrays recursively
		 *
		 * @param $array
		 * @param $array1
		 *
		 * @return mixed
		 */
		function tve_array_recurse( $array, $array1 ) {

			if ( tve_is_numeric_array( $array ) && tve_is_numeric_array( $array1 ) ) {
				/* if both arrays are numeric, we don't concatenate them, we just return the second one */
				return $array1;
			}

			foreach ( $array1 as $key => $value ) {
				/* create new key in $array, if it is empty or not an array */
				if ( ! isset( $array[ $key ] ) || ( isset( $array[ $key ] ) && ! is_array( $array[ $key ] ) ) ) {
					$array[ $key ] = array();
				}

				/* overwrite the value in the base array */
				if ( is_array( $value ) ) {
					$value = tve_array_recurse( $array[ $key ], $value );
				}
				$array[ $key ] = $value;
			}

			return $array;
		}
	}

	/* handle the arguments, merge one by one */
	$args  = func_get_args();
	$array = $args[0];

	if ( ! is_array( $array ) ) {
		return $array;
	}

	for ( $i = 1, $length = count( $args ); $i < $length; $i ++ ) {
		if ( is_array( $args[ $i ] ) ) {
			$array = tve_array_recurse( $array, $args[ $i ] );
		}
	}

	return $array;
}

if ( ! function_exists( 'tve_frontend_enqueue_scripts' ) ) {

	/**
	 * enqueue scripts for the frontend - also editor and preview
	 */
	function tve_frontend_enqueue_scripts() {
		$post_id   = get_the_ID();
		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		if ( ! apply_filters( 'tcb_overwrite_scripts_enqueue', false ) && ! is_editor_page_raw() ) {
			/**
			 * enqueue scripts and styles only for posts / pages that actually have tcb content
			 */
			global $wp_query;
			if ( empty( $wp_query->posts ) ) {
				return;
			}
			$enqueue_tcb_resources = false;
			foreach ( $wp_query->posts as $_post ) {
				if ( tve_get_post_meta( $_post->ID, 'tve_updated_post' ) ) {
					$enqueue_tcb_resources = true;
					break;
				}
			}
			$enqueue_tcb_resources = apply_filters( 'tcb_enqueue_resources', $enqueue_tcb_resources );
			if ( ! $enqueue_tcb_resources ) {
				if ( ! is_singular() ) {
					return;
				}
				/* check also if we have page events, e.g. open lightbox on exit intent */
				$events = tve_get_post_meta( get_the_ID(), 'tve_page_events' );
				if ( empty( $events ) ) {
					/* no events defined -> safe to return here */
					return;
				}
			}
		}

		/**
		 * Enqueue some dash scripts in the editor page
		 */
		if ( is_editor_page() ) {
			tve_enqueue_script( 'jquery-zclip', TVE_DASH_URL . '/js/util/jquery.zclip.1.1.1/jquery.zclip.min.js', array( 'jquery' ) );
		}

		tve_enqueue_style_family();
		wp_enqueue_script( 'plupload' );
		tve_enqueue_script( 'tve_frontend', tve_editor_js() . '/frontend' . $js_suffix, array( 'jquery', 'jquery-masonry', 'plupload' ), false, true );

		if ( apply_filters( 'tcb_overwrite_event_scripts_enqueue', false ) || ( ! is_editor_page() && is_singular() ) ) {
			$events = tve_get_post_meta( get_the_ID(), 'tve_page_events' );
			if ( ! empty( $events ) && is_array( $events ) ) {
				tve_page_events( $events );
			}
		}

		/* params for the frontend script */
		$frontend_options = array(
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'is_editor_page'   => is_editor_page(),
			'page_events'      => isset( $events ) ? $events : array(),
			'is_single'        => (string) ( (int) is_singular() ),
			'social_fb_app_id' => tve_get_social_fb_app_id(),
			'dash_url'         => TVE_DASH_URL,
			'translations'     => array(
				'Copy'             => __( 'Copy', 'thrive-cb' ),
				'empty_username'   => __( 'ERROR: The username field is empty.', 'thrive-cb' ),
				'empty_password'   => __( 'ERROR: The password field is empty.', 'thrive-cb' ),
				'empty_login'      => __( 'ERROR: Enter a username or email address.', 'thrive-cb' ),
				'min_chars'        => __( 'At least %s characters are needed', 'thrive-cb' ),
				'no_headings'      => __( 'No headings found', 'thrive-cb' ),
				'registration_err' => array(
					'required_field'   => __( '<strong>Error</strong>: This field is required', 'thrive-cb' ), // generic error message
					'required_email'   => __( '<strong>Error</strong>: Please type your email address.' ), //default WP message
					'invalid_email'    => __( '<strong>Error</strong>: The email address isn&#8217;t correct.' ), //default WP message
					'passwordmismatch' => __( '<strong>Error</strong>: Password mismatch', 'thrive-cb' ),
				),
			),
			'routes'           => array(
				'posts' => get_rest_url( get_current_blog_id(), 'tcb/v1' . '/posts' ),
			),

		);

		tve_enqueue_social_scripts();
		// hide tve more tag from front end display
		if ( ! $frontend_options['is_editor_page'] ) {
			tve_enqueue_custom_fonts();
			tve_enqueue_custom_scripts();
			$frontend_options['post_request_data'] = empty( $_POST ) ? array() : $_POST;
		}

		/**
		 * Allows adding frontend options from different plugins
		 *
		 * @param $frontend_options
		 */
		$frontend_options = apply_filters( 'tve_frontend_options_data', $frontend_options );

		wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );

		do_action( 'tve_frontend_extra_scripts' );
		if ( is_singular() && tcb_landing_page( $post_id )->should_remove_theme_css() && tve_membership_plugin_can_display_content() ) {
			add_action( 'wp_print_styles', 'tve_remove_theme_css', PHP_INT_MAX );
			tve_enqueue_style( 'the_editor_no_theme', tve_editor_css() . '/no-theme.css' );
		}
	}
}

/**
 * Return the default args for displaying a widget
 *
 * @return array
 */
function tve_get_sidebar_default_args( $widget = null ) {
	global $wp_registered_sidebars, $widget_id_count;

	$widget_id_count = empty( $widget_id_count ) ? 1 : $widget_id_count ++;

	$args = array(
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
		'widget_id'     => $widget->id_base,
	);

	if ( is_array( $wp_registered_sidebars ) && count( $wp_registered_sidebars ) ) {
		$sidebar = current( $wp_registered_sidebars );

		if ( isset( $sidebar['before_widget'] ) ) {
			$args['before_widget'] = empty( $widget ) ? $sidebar['before_widget'] : sprintf( $sidebar['before_widget'], $widget->id_base . '-' . $widget_id_count, $widget->widget_options['classname'] );
		}

		$args['after_widget'] = isset( $sidebar['after_widget'] ) ? $sidebar['after_widget'] : $args['after_widget'];
		$args['before_title'] = isset( $sidebar['before_title'] ) ? $sidebar['before_title'] : $args['before_title'];
		$args['after_title']  = isset( $sidebar['after_title'] ) ? $sidebar['after_title'] : $args['after_title'];
	}

	return $args;
}

/**
 * Render widget shortcode
 *
 * @param $data
 *
 * @return string
 */
function thrive_widget_render( $data ) {
	global $wp_widget_factory;

	if ( empty( $data ) || empty( $data['type'] ) ) {
		return '';
	}

	$content = '';

	foreach ( $wp_widget_factory->widgets as $widget ) {
		if ( $widget->option_name === $data['type'] ) {
			ob_start();
			$widget->widget( tve_get_sidebar_default_args( $widget ), $data );
			$content = ob_get_contents();
			ob_get_clean();
		}
	}

	return $content;
}

function tve_enqueue_icon_pack() {
	TCB_Icon_Manager::enqueue_icon_pack();
}

/**
 * The purpose of this function is for debugging
 *
 * Checks if the plugin is in debugging mode
 *
 * @return bool
 */
function tve_is_code_debug() {
	$constant = defined( 'TVE_CODE_DEBUG' ) && TVE_CODE_DEBUG;
	$file     = file_exists( ABSPATH . '.thrive-debug' );

	return $constant || $file;
}

/**
 * Register rest routes for admin dashboard
 */
function tcb_create_admin_rest_routes() {
	require_once TVE_TCB_ROOT_PATH . 'admin/includes/class-tcb-symbols-rest-controller.php';

	$endpoints = array(
		'TCB_REST_Symbols_Controller',
	);
	foreach ( $endpoints as $e ) {
		$controller = new $e();
		$controller->register_routes();
	}
}

/**
 * Check if the WordPress version is at least what's needed for TAr to run
 *
 * @return bool
 */
function tcb_wordpress_version_check() {

	return version_compare( get_bloginfo( 'version' ), TCB_MIN_WP_VERSION, '>=' );
}

/**
 * Hook into TD's DB migrations manager and register TAr migrations
 *
 * @throws Exception
 */
function tcb_prepare_db_migrations() {
	TD_DB_Manager::add_manager(
		tve_editor_path( 'db' ),
		'tve_tcb_db_version',
		TVE_TCB_DB_VERSION,
		'Thrive Architect',
		'tcb_'
	);
}

/**
 * Will return true only if current code is executed from the TAr plugin
 *
 * @return bool
 */
function tve_in_architect() {
	return defined( 'TVE_IN_ARCHITECT' ) && TVE_IN_ARCHITECT;
}

/**
 * Whether or not the current user has access to thrive architect features (e.g. manage global templates)
 *
 * @param bool $return_cap controls the return type
 *
 * @return bool|string
 */
function tcb_has_external_cap( $return_cap = false ) {
	$cap = '';
	foreach ( tve_dash_get_products() as $product ) {
		/** TVE_Dash_Product_Abstract $product */
		if ( $product->needs_architect() && current_user_can( $product->get_cap() ) ) {

			$cap = $product->get_cap();
			break;
		}
	}

	if ( $return_cap ) {
		return $cap;
	}

	return ! empty( $cap );
}

/**
 * make sure the TCB product is shown in the dashboard product list
 *
 * @param array $items
 *
 * @return array
 */
function tcb_add_to_dashboard_list( $items ) {
	$items[] = new TCB_Product();

	return $items;
}

/**
 * Called after dash has been loaded
 */
function tcb_dashboard_loaded() {
	require_once TVE_TCB_ROOT_PATH . 'admin/includes/class-tcb-product.php';
}

/**
 * save the list of downloaded templates into the wp_option used for these
 *
 * @param array $templates
 */
function tve_save_downloaded_templates( $templates ) {
	update_option( 'thrive_tcb_download_lp', $templates );
}

/**
 * Returns default global css prefix
 *
 * @param $always bool apply selector all the time
 *
 * @return mixed|void
 */
function tcb_selection_root( $always = true ) {
	/**
	 * Possibility to change global css prefix selector
	 *
	 * @param string TVE_GLOBAL_CSS_PREFIX default global css prefix
	 * @param bool param whether to apply the selector all the time
	 */
	return apply_filters( 'tcb_selection_root', TVE_GLOBAL_CSS_PREFIX, $always );
}

/**
 * Change css for old symbols / headers / footers ( the ones saved before the tve_editor was changed )
 * In the past each time we had #tve_editor inside the selector, in the symbols we would add two classes .thrv_symbol.thrv_symbol_{id} and for the other cases we would just have thrv_symbol_{id}
 * Now we change the selectors for the elements inside a symbol and instead of the two classes we would have $global_selector( :not(#tve) ) + .thrv_symbol_{id}.
 * The rest of the elements which don't need a global selector will have only thrv_symbol_{id}
 *
 * For headers and footers we will just replace .thrv_symbol.thrv_header with thrv_symbol_{id} for the same reason
 *
 * @param $css
 * @param $id
 *
 * @return mixed|string|string[]|null
 */
function symbols_css_backwards_compatible( $css, $id ) {
	$global_selector = tcb_selection_root();

	/**
	 * Backwards compatibility with previous saved symbols
	 * Add global css prefix when the selector has two classes
	 */
	if ( strpos( $css, $global_selector ) === false ) {
		$pattern = '/\.thrv_symbol\.thrv_symbol_\d*/';
		$css     = preg_replace( $pattern, $global_selector . ' .thrv_symbol_' . $id, $css );
	}

	/**
	 * Backwards compatibility with previous saved headers / footers
	 */
	if ( strpos( $css, '.thrv_header' ) !== false || strpos( $css, '.thrv_footer' ) !== false ) {
		$css = str_replace( array( '.thrv_symbol.thrv_header', '.thrv_symbol.thrv_footer' ), " .thrv_symbol_{$id}", $css );
	}

	return $css;
}

/**
 * Get default styles saved from TAr. Makes sure the returned data always has the same structure
 *
 * @param bool $include_imports whether or not to include @imports node in the returned data
 *
 * @return array
 */
function tve_get_default_styles( $include_imports = true ) {
	/**
	 * Filter. Allows dynamically adding default styles for TAr elements
	 *
	 * @param array $styles          list of existing styles, per element type
	 * @param bool  $include_imports whether or not to include @imports node in the returned data
	 *
	 * @return array
	 */
	$styles = apply_filters( 'tcb_default_styles', tcb_default_style_provider()->get_styles(), $include_imports );
	if ( ! $include_imports ) {
		unset( $styles['@imports'] );
	}

	return $styles;
}

/**
 * Prepares the default styles for printing in the style node
 * Used in the global styles CSS node
 *
 * @return array
 */
function tve_prepare_default_styles() {
	return tcb_default_style_provider()->get_processed_styles();
}

/**
 * Instantiates a default style provider
 *
 * @return TCB_Style_Provider
 */
function tcb_default_style_provider() {
	static $tcb_default_style_provider;

	if ( ! $tcb_default_style_provider ) {
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-tcb-style-provider.php';
		$tcb_class = 'TCB_Style_Provider';

		/**
		 * Allows having custom default style providers
		 *
		 * @param string $style_provider_class class that should be instantiated
		 */
		$style_provider_class = apply_filters( 'tcb_default_style_provider_class', $tcb_class );
		/* some extra checks just to make sure this is of required type */
		if ( ! class_exists( $style_provider_class, false ) ) {
			$style_provider_class = $tcb_class;
		}

		$tcb_default_style_provider = new $style_provider_class();

		if ( ! ( $tcb_default_style_provider instanceof TCB_Style_Provider ) ) {
			$tcb_default_style_provider = new TCB_Style_Provider();
		}
	}

	return $tcb_default_style_provider;
}

/**
 * Checks if the given post type is not blacklisted
 *
 * @param $is_allowed
 * @param $post_type
 *
 * @return bool
 */
function tar_is_post_type_allowed( $is_allowed, $post_type ) {
	$blacklisted_post_types = array(
		'post',
		'attachment',
		'revision',
		'project',
		'et_pb_layout',
		'nav_menu_item',
		'focus_area',
		'tcb_lightbox',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'user_request',
		'wp_block',
		'tcb_content_template',
		'tcb_symbol',
		'td_nm_notification',
		'tve_form_type',
	);
	$is_allowed             = ! in_array( $post_type, apply_filters( 'tcb_post_grid_banned_types', $blacklisted_post_types ) );

	return $is_allowed;
}

add_filter( 'wp_kses_allowed_html', 'tcb_allow_unfiltered_html', 20, 2 );

/**
 * WordPress puts a filter and limits what html can be used on any content that is added by a non-superadmin in a multisite system.
 * SVGs will get stripped out by this filter, and certain tags ( such as <main> ) are not allowed
 *
 * Allows for svg attributes in html for authors and non super admin users.
 * Also allows <main> tags ( + data-attributes for them )
 * todo: this can be deleted after the 26.02.2020 release, because it's fixed directly on the themes-build multisite
 *
 * @param      $tags
 * @param null $context
 *
 * @return mixed
 */
function tcb_allow_unfiltered_html( $tags, $context = null ) {
	if ( isset( $context ) && 'post' === $context && current_user_can( 'edit_posts' ) ) {
		$tags['svg'] = array(
			'aria-hidden'         => true,
			'aria-labelledby'     => true,
			'class'               => true,
			'data-position'       => true,
			'data-ct'             => true,
			'data-css'            => true,
			'decoration-type'     => true,
			'fill'                => true,
			'focusable'           => true,
			'height'              => true,
			'id'                  => true,
			'preserveaspectratio' => true,
			'role'                => true,
			'stroke'              => true,
			'stroke-width'        => true,
			'stroke-linecap'      => true,
			'stroke-linejoin'     => true,
			'style'               => true,
			'viewBox'             => true,
			'viewbox'             => true,
			'version'             => true,
			'width'               => true,
			'x'                   => true,
			'xmlns'               => true,
			'xmlns:xlink'         => true,
			'xml:space'           => true,
			'y'                   => true,
		);

		$tags['path'] = array(
			'd'       => true,
			'opacity' => true,
			'fill'    => true,
			'class'   => true,
		);

		$tags['circle'] = array(
			'cx' => true,
			'cy' => true,
			'r'  => true,
		);
		$tags['rect']   = array(
			'x'      => true,
			'y'      => true,
			'width'  => true,
			'height' => true,
			'rx'     => true,
			'ry'     => true,
			'class'  => true,
		);

		$tags['line'] = array(
			'class'             => true,
			'stroke'            => true,
			'stroke-linecap'    => true,
			'x1'                => true,
			'y1'                => true,
			'x2'                => true,
			'y2'                => true,
			'data-temp-xa-hash' => true,
			'data-temp-ya-hash' => true,
			'data-temp-xb-hash' => true,
			'data-temp-yb-hash' => true,
		);

		$tags['polygon'] = array(
			'points' => true,
			'fill'   => true,
			'class'  => true,
		);

		$tags['polyline'] = array(
			'points' => true,
			'fill'   => true,
		);

		$tags['title'] = array(
			'title' => true,
		);

		$tags['defs'] = array(
			'id' => true,
		);

		$tags['g'] = array(
			'fill'      => true,
			'id'        => true,
			'data-name' => true,
			'class'     => true,
		);

		$tags['style'] = array(
			'class' => true,
			'id'    => true,
			'type'  => true,
		);

		/* this is for the post list wrapper todo only for backwards compat now, the post list tag became <div> */
		$tags['main'] = array(
			'id'                                  => true,
			'data-query'                          => true,
			'data-type'                           => true,
			'data-columns-d'                      => true,
			'data-columns-t'                      => true,
			'data-columns-m'                      => true,
			'data-vertical-space-d'               => true,
			'data-horizontal-space-d'             => true,
			'data-ct'                             => true,
			'data-ct-name'                        => true,
			'data-tcb-elem-type'                  => true,
			'data-pagination-type'                => true,
			'data-pages_near_current'             => true,
			'data-css'                            => true,
			'class'                               => true,
			'data-article-tcb_hover_state_parent' => true,
		);

		if ( empty( $tags['input'] ) ) {
			$tags['input'] = array();
		}

		$tags['input'] = array_merge( $tags['input'], array(
			'placeholder'      => true,
			'data-placeholder' => true,
			'type'             => true,
			'name'             => true,
			'style'            => true,
		) );
	}

	return $tags;
}

/**
 * Whether or not TAr should print unified styles in the head section
 * - a global fonts section (including all google fonts used throughout the page)
 * - a "default" style node, containing default styles
 *
 * This is currently true for:
 * -> any page that's NOT an editor page
 * -> landing pages, but ONLY if "Do not strip head css" has been ticked
 *
 * @return bool
 */
function tcb_should_print_unified_styles() {
	/**
	 * Filter allows printing default styles in various scenarios
	 *
	 * @param bool $value whether or not to print styles
	 */
	return apply_filters( 'tcb_should_print_unified_styles', ! is_editor_page_raw() && ( ! is_singular() || ! tcb_post()->is_landing_page() || ! tcb_landing_page( get_the_ID() )->should_strip_head_css() ) );
}

/**
 * Called during 'wp_head', outputs used google fonts and default styles
 * Outputs a style node with user-defined default styles
 *
 * !Only on FRONTEND ( NOT on editor pages )
 */
function tcb_print_frontend_styles() {
	/* external (google) fonts */
	$font_imports = tcb_default_style_provider()->get_css_imports();

	/**
	 * Filter all fonts used on the current page
	 *
	 * @param array $fonts array
	 */
	$font_imports = apply_filters( 'tcb_css_imports', $font_imports );

	if ( ! tve_dash_is_google_fonts_blocked() ) {
		$font_imports = TCB_Utils::merge_google_fonts( $font_imports, 'link' );

		foreach ( $font_imports as $url ) {
			echo '<link type="text/css" rel="stylesheet" class="thrive-external-font" href="' . $url . '">';
		}
	}

	/* Default Styles node */
	echo sprintf( '<style type="text/css" id="thrive-default-styles">%s</style>', tcb_default_style_provider()->get_processed_styles( null, 'string', false ) );
}

/**
 * export a Landing Page as a Zip file
 */
function tve_ajax_landing_page_export() {
	$response = array(
		'success' => true,
	);

	if ( empty( $_POST['template_name'] ) || empty( $_POST['post_id'] ) || ! is_numeric( $_POST['post_id'] ) || ! tve_post_is_landing_page( $_POST['post_id'] ) ) {
		$response['success'] = false;
		$response['message'] = __( 'Invalid request', 'thrive-cb' );
		wp_send_json( $response );
	}

	$transfer = new TCB_Landing_Page_Transfer();

	$thumb_attachment_id = empty( $_POST['thumb_id'] ) ? 0 : (int) $_POST['thumb_id'];

	try {

		$data                = $transfer->export( (int) $_POST['post_id'], $_POST['template_name'], $thumb_attachment_id );
		$response['url']     = $data['url'];
		$response['message'] = __( 'Landing Page exported successfully!', 'thrive-cb' );

	} catch ( Exception $e ) {
		$response['success'] = false;
		$response['message'] = $e->getMessage();
	}

	wp_send_json( $response );
}

/**
 * import a landing page from an attachment ID received in POST
 * the attachment should be a .zip file created with the "Export Landing Page" functionality
 */
function tve_ajax_landing_page_import() {
	$response = array(
		'success' => true,
		'message' => '',
	);

	$is_post_type_allowed = apply_filters( 'tve_allowed_post_type', true, get_post_type( $_POST['page_id'] ) );
	if ( empty( $_POST['attachment_id'] ) || ! is_numeric( $_POST['attachment_id'] ) || empty( $_POST['page_id'] ) || ! is_numeric( $_POST['page_id'] ) || ! $is_post_type_allowed ) {
		$response['success'] = false;
		$response['message'] = __( 'Invalid attachment id', 'thrive-cb' );
		wp_send_json( $response );
	}

	$transfer = new TCB_Landing_Page_Transfer();
	try {
		$file                = get_attached_file( (int) $_POST['attachment_id'], true );
		$landing_page_id     = $transfer->import( $file, (int) $_POST['page_id'] );
		$response['url']     = tcb_get_editor_url( $landing_page_id );
		$response['message'] = __( 'Landing Page imported successfully!', 'thrive-cb' );

	} catch ( Exception $e ) {
		$response['success'] = false;
		$response['message'] = $e->getMessage();
	}

	wp_send_json( $response );
}

/**
 * Get a dynamic link based on its name
 *
 * @param string $field_name
 * @param string $section
 *
 * @return string|false
 */
function tcb_get_dynamic_link( $field_name, $section ) {

	/**
	 * Get all dynamic links available
	 *
	 * $param array
	 *
	 * @return array
	 */
	$dynamicLinks = apply_filters( 'tcb_dynamiclink_data', array() );

	if ( ! isset( $dynamicLinks[ $section ]['links'][0] ) ) {
		return false;
	}

	$links = $dynamicLinks[ $section ]['links'][0];

	foreach ( $links as $link ) {
		if ( $field_name === $link['name'] ) {
			return $link;
		}
	}

	return false;
}

/**
 * Adding name field to favourite colors array
 *
 * @return array
 */
function tve_convert_favorite_colors() {
	$favouriteColorsArray = get_option( 'thrv_custom_colours', array() );
	array_walk( $favouriteColorsArray, function ( &$color ) {
		if ( ! is_array( $color ) ) {
			$color = array( 'rgb' => $color, 'name' => 'Favourite color', 'default' => 1 );
		}

		return $color;
	} );

	return $favouriteColorsArray;
}

/**
 * Return the author social urls for the current author
 *
 * @return array
 */
function tve_author_social_url() {
	global $post;

	return empty( $post ) ? array() : (array) get_the_author_meta( 'thrive_social_urls', $post->post_author );
}
