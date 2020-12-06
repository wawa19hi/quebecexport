<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Custom_Fields_Shortcode
 */
class TCB_Custom_Fields_Shortcode {
	const GLOBAL_SHORTCODE_URL = 'thrive_custom_fields_shortcode_url';
	const GLOBAL_SHORTCODE_DATA = 'thrive_custom_fields_shortcode_data';

	private $pattern_replacement = array(
		'[&]'  => '::',
		'[/]'  => ';;',
		'[<]'  => ';:',
		'[>]'  => ':;',
		'[\[]' => '*;',
		'[\]]' => ';*',
		'[\']' => ':*',
		'[\"]' => '*:',
		'[ ]'  => '**',
	);

	const ACF_PREFIX = 'acf_';

	private $field_types = array(
		'link'      => array( 'text', 'image', 'email', 'url', 'file', 'page_link', 'link' ),
		'image'     => array( 'image', 'text' ),
		'text'      => array( 'text', 'textarea', 'number', 'range', 'email', 'url', 'password', 'true_false', 'date_picker', 'date_time_picker', 'time_picker', 'checkbox' ),
		'video'     => array( 'file', 'url', 'text' ),
		'audio'     => array( 'file', 'text' ),
		'map'       => array( 'map', 'text' ),
		'countdown' => array( 'date_time_picker' ),
		'number'    => array( 'number', 'range' ),
		'color'     => array( 'color_picker' ),
	);

	private $postlist_field_types = array(
		'link'      => array( 'text', 'image', 'email', 'url', 'file', 'page_link', 'link' ),
		'image'     => array( 'image', 'text' ),
		'text'      => array( 'text', 'textarea', 'number', 'range', 'email', 'url', 'password', 'true_false', 'date_picker', 'date_time_picker', 'time_picker', 'checkbox' ),
		'number'    => array( 'number', 'range' ),
		'countdown' => array( 'date_time_picker' ),
		'video'     => array( 'file', 'url', 'text' ),
		'audio'     => array( 'file', 'text' ),
		'color'     => array( 'color_picker' ),
	);

	/* some WooCommerce fields we're here, we've created a new category for them */
	public static $whitelisted_fields = array();

	public static $blacklisted_fields = array(
		'\_%',
		'thrive%',
		'tcb%',
		'wp%',
		'tve%',
	);

	public static $protected_fields = array(
		'_',                    //General protected metadata starts with '_'
		'thrive_',              //Thrive Architect metadata
		'thrv_',
		'tve_',
		'td_nm_',
		'tcb_',
		'tcb2_',
		'tcm_',                 //Thrive Comments metadata
		'tva_',                 //Thrive Apprentice metadata
		'tu_',                  //Thrive Ultimate metadata
		'tqb_',                 //Thrive Quiz Builder metadata
		'tvo_',                 //Thrive Ovation metadata
		'_tho',                 //Thrive Headline Optimiser metadata
		'is_control',           //Thrive Optimize metadata
		'sections',
		/**  Protected Metadata for other plugins**/

		'total_sales',          //WooCommerce metadata
		'rank_math_',           //Rank Math SEO metadata
		'pb_original_content',  //PageBuilder meta content breaks editor localization
	);

	private $video_regex = array(
		'/https?:\/\/(.+)\.(cdn\.(vooplayer|spotlightr)\.com)\/publish\/(.+)/'                                                                  => 'vooplayer',
		'/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/'                => 'youtube',
		'/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|list\/|playlist\?list=|playlist\?.+&list=))((\w|-){18})(?:\S+)?$/' => 'youtube',
		'/(http|https)?:\/\/(www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|)(\d+)(?:|\/\?)/'                             => 'vimeo',
		'/https?:\/\/(.+)?(wistia.com|wi.st)\/(?:medias|embed)\/(.+)/'                                                                          => 'wistia',
		'/https?:\/\/(.+)?fast.wistia.net\/embed\/(.+?)\/(.+)/'                                                                                 => 'wistia',
	);

	/**
	 * Holds the value of Custom Fields Plugin - External Fields
	 *
	 * @var array
	 */
	private $external_fields;

	/**
	 * @var TCB_Custom_Fields_Shortcode
	 */
	private static $instance;

	/**
	 * Holds the value of the user shortcodes
	 *
	 * @var array
	 */
	private $user_shortcodes = array();

	/**
	 * Singleton implementation for TCB_Custom_Fields_Shortcode
	 *
	 * @return TCB_Custom_Fields_Shortcode
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * TCB_Custom_Fields_Shortcode constructor.
	 */
	private function __construct() {

		/**
		 * User Shortcodes Configuration
		 */
		$this->user_shortcodes = array(
			'tcb_username_field'   => array(
				'name'             => __( 'Username', 'thrive-cb' ),
				'property'         => 'user_login',
				'not_logged_value' => __( 'Username', 'thrive-cb' ),
			),
			'tcb_first_name_field' => array(
				'name'             => __( 'First Name', 'thrive-cb' ),
				'property'         => 'user_firstname',
				'not_logged_value' => __( 'John', 'thrive-cb' ),
			),
			'tcb_last_name_field'  => array(
				'name'             => __( 'Last Name', 'thrive-cb' ),
				'property'         => 'user_lastname',
				'not_logged_value' => __( 'Doe', 'thrive-cb' ),
			),
		);

		/**
		 * Filters
		 */
		add_filter( 'tcb_content_allowed_shortcodes', array( $this, 'allowed_shortcodes' ) );
		add_filter( 'tcb_dynamiclink_data', array( $this, 'global_links_shortcodes' ) );
		add_filter( 'tcb_inline_shortcodes', array( $this, 'tcb_inline_shortcodes' ), 11 );

		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'alter_custom_fields_image_attributes' ) );

		/**
		 * Adds shortcodes callbacks
		 */
		add_shortcode( static::GLOBAL_SHORTCODE_URL, array( $this, 'global_shortcode_url_link' ) );
		add_shortcode( static::GLOBAL_SHORTCODE_DATA, array( $this, 'global_shortcode_url_data' ) );
		add_shortcode( 'tcb_custom_field', array( $this, 'render_custom_fields' ) );
		add_shortcode( 'tcb_dynamic_field', array( $this, 'render_dynamic_field' ) );

		foreach ( $this->user_shortcodes as $key => $name ) {
			add_shortcode( $key, array( $this, 'render_user_shortcode' ) );
		}

		add_action( 'tcb_get_extra_global_variables', array( $this, 'output_custom_fields_variables' ) );

		add_filter( 'tcb_main_frame_localize', array( $this, 'custom_fields_localization' ) );

		add_filter( 'tcb_get_post_list_variables', array( $this, 'output_post_list_variables' ), 10, 2 );

		/** Added ACF script to solve error thrown by ACF 5.9 update */
		add_action( 'tcb_main_frame_enqueue', static function () {
			wp_enqueue_script( 'acf' );
		} );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function custom_fields_localization( $data = array() ) {
		if ( $this->has_external_fields_colors() ) {
			$external_fields                 = $this->get_all_external_fields();
			$data['colors']['custom_fields'] = $this->prepare_custom_fields_colors( get_the_ID(), $external_fields['color'] );
			$data['has_acf_colors']          = 1;
		}

		$data['acf_is_active'] = $this->has_external_fields_plugins() ? 1 : 0;

		return $data;
	}

	/**
	 * Prepares the Custom Fields Colors for TAR
	 *
	 * @param       $post_id
	 * @param array $colors
	 *
	 * @return array
	 */
	public function prepare_custom_fields_colors( $post_id, $colors = array() ) {
		$return = array();

		/**
		 * For Post List Element the $id_suffix is empty because it needs to be propagated along all posts
		 */
		$id_suffix = ( empty ( $post_id ) ? '' : '_' . $post_id );

		foreach ( $colors as $field_id => $field_value ) {
			$return[] = array(
				'active'      => 1,
				'custom_name' => 1,
				'color'       => $field_value['value'],
				'id'          => sanitize_title( $field_id ) . $id_suffix,
				'name'        => sanitize_title( $field_value['label'] ),
				'label'       => $field_value['label'],
			);
		}

		return $return;
	}

	/**
	 * Outputs the post list variables inside the post list dynamic style
	 *
	 * @param string $variables
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public function output_post_list_variables( $variables, $post_id ) {
		$post_list_fields = $this->get_all_external_postlist_fields( $post_id );

		$variables .= $this->get_custom_fields_variables( $post_list_fields );

		return $variables;
	}

	/**
	 * Output the custom fields - color variables
	 */
	public function output_custom_fields_variables() {
		$external_fields = $this->get_all_external_fields();
		$suffix          = '_' . get_the_ID();

		echo $this->get_custom_fields_variables( $external_fields, $suffix );
	}

	/**
	 * Used for getting custom fields variables
	 *
	 * Used for PostList Element and general content
	 *
	 * @param array  $external_fields
	 * @param string $suffix
	 *
	 * @return string
	 */
	private function get_custom_fields_variables( $external_fields = array(), $suffix = '' ) {
		$variables = '';

		if ( ! empty( $external_fields['color'] ) && is_array( $external_fields['color'] ) ) {
			foreach ( $external_fields['color'] as $field_id => $field_value ) {
				/* fallback for the dynamic color */
				$fallback = 'hsl(var(--tcb-main-master-h,0), var(--tcb-main-master-s,0%),var(--tcb-main-master-l,4%))';

				if ( empty( $field_value['value'] ) ) {
					$field_value['value'] = $fallback;
				}

				$variables .= TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX . sanitize_title( $field_id ) . $suffix . ':' . $field_value['value'] . ';';
				$variables .= TVE_DYNAMIC_COLOR_VAR_CSS_PREFIX . sanitize_title( $field_id ) . $suffix . '-default' . ':' . $fallback . ';';
			}
		}

		return $variables;
	}

	/**
	 * Check if the user has saved colors as external fields
	 *
	 * @return bool
	 */
	public function has_external_fields_colors() {
		if ( ! $this->has_external_fields_plugins() ) {
			return false;
		}

		$external_fields = $this->get_all_external_fields();

		return ! empty( $external_fields['color'] );
	}

	/**
	 * Check if there exists external fields plugins activated
	 *
	 * @return bool
	 */
	public function has_external_fields_plugins() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return is_plugin_active( 'advanced-custom-fields/acf.php' ) || is_plugin_active( 'advanced-custom-fields-pro/acf.php' );
	}

	/**
	 * Removes the srcset and sizes from images from editor page
	 *
	 * @param array $attr
	 *
	 * @return array
	 */
	public function alter_custom_fields_image_attributes( $attr = array() ) {
		if ( is_editor_page_raw( true ) && ( isset( $attr['data-d-f'] ) || isset( $attr['data-c-f-id'] ) ) ) {
			unset( $attr['srcset'], $attr['sizes'] );
		}

		return $attr;
	}

	/**
	 * Endpoint for render dynamic fields shortcode
	 *
	 * @param array $args
	 * @param       $content
	 * @param       $tag
	 *
	 * @return string
	 */
	public function render_dynamic_field( $args = array(), $content, $tag ) {

		if ( TCB_Post_List::is_outside_post_list_render() && isset( $args['post_list'] ) ) {

			return '[' . $tag . ' ' . implode( ' ', array_map(
					function ( $k, $v ) {
						return $k . '="' . htmlspecialchars( $v ) . '"';
					},
					array_keys( $args ), $args
				) ) . ']';
		}

		if ( is_array( $args ) && ! empty( $args['type'] ) && is_string( $args['type'] ) ) {
			$method_name = 'render_dynamic_field_' . $args['type'];

			if ( method_exists( $this, $method_name ) ) {
				return $this->$method_name( $args );
			}
		}
	}

	/**
	 * Renders the dynamic filed author
	 *
	 * @return string
	 */
	private function render_dynamic_field_author( $args = array() ) {

		$post_title  = the_title_attribute( array( 'echo' => 0 ) );
		$post_author = get_post_field( 'post_author', get_the_ID() );

		$args['alt']          = ! empty( $args['alt'] ) ? $args['alt'] : $post_title;
		$args['title']        = ! empty( $args['title'] ) ? $args['title'] : $post_title;
		$args['data-classes'] = ! empty( $args['data-classes'] ) ? $args['data-classes'] : 'tve_image';
		$args['data-css']     = ! empty( $args['data-css'] ) ? $args['data-css'] : '';

		return get_avatar( $post_author, 256, '', $args['alt'], array(
			'class'      => $args['data-classes'],
			'extra_attr' => 'loading="lazy" data-d-f="author" title="' . $args['title'] . '" width="500" height="500" data-css="' . $args['data-css'] . '"',
		) );
	}

	/**
	 * Renders the dynamic filed featured image
	 *
	 * @return string
	 */
	private function render_dynamic_field_featured( $args = array() ) {
		$featured_image_url = tve_editor_url( 'editor/css/images/featured_image.png' );
		$post_title         = the_title_attribute( array( 'echo' => 0 ) );

		$args['alt']      = ! empty( $args['alt'] ) ? $args['alt'] : $post_title;
		$args['title']    = ! empty( $args['title'] ) ? $args['title'] : $post_title;
		$args['data-css'] = ! empty( $args['data-css'] ) ? $args['data-css'] : '';
		if ( has_post_thumbnail() ) {
			$thumbnail_id         = get_post_thumbnail_id();
			$args['data-classes'] = ! empty( $args['data-classes'] ) ? $args['data-classes'] : 'tve_image wp-image-' . $thumbnail_id;

			return wp_get_attachment_image( $thumbnail_id, 'full', false, array(
				'class'    => $args['data-classes'],
				'title'    => $args['title'],
				'alt'      => $args['alt'],
				'data-id'  => $thumbnail_id,
				'data-d-f' => 'featured',
				'loading'  => 'lazy',
				'data-css' => $args['data-css'],
			) );
		}
		$args['data-classes'] = ! empty( $args['data-classes'] ) ? $args['data-classes'] : 'tve_image';

		return '<img loading="lazy" class="' . $args['data-classes'] . '" alt="' . $args['alt'] . '" data-id="' . 0 . '" data-d-f="featured" width="500" height="500" title="' . $args['title'] . '" src="' . $featured_image_url . '" data-css="' . $args['data-css'] . '">';
	}

	/**
	 * Endpoint for render custom fields shortcode
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function render_custom_fields( $args = array(), $params = null ) {

		if ( is_array( $args ) && ! empty( $args['data-id'] ) && ! empty( $args['data-field-type'] ) && is_string( $args['data-field-type'] ) ) {

			$method_name = 'render_custom_fields_' . $args['data-field-type'];

			if ( method_exists( $this, $method_name ) ) {
				return $this->$method_name( $args, $params ); //TODO: maybe do not send all the args and send only what is needed. Enhanced CF part 3
			}
		}
	}

	/**
	 * Returns the custom fields shortcode parameters shortcode parameters
	 *
	 * @param        $shortcode_id
	 * @param string $type
	 *
	 * @return array
	 */
	private function get_custom_fields_shortcode_params( $shortcode_id, $type = '' ) {

		$custom_fields = $this->get_all_external_fields();

		$params = ! empty( $custom_fields[ $type ][ $shortcode_id ] ) && is_array( $custom_fields[ $type ][ $shortcode_id ] ) ? $custom_fields[ $type ][ $shortcode_id ] : array();

		return $params;
	}

	public function render_custom_fields_map( $args = array(), $param = null ) {

		$params = $this->get_custom_fields_shortcode_params( $args['data-id'], 'map' );

		if ( empty( $params ) ) {
			$params = array(
				'latitude'  => 0,
				'longitude' => 0,
				'zoom'      => 0,
			);

			if ( isset( $args['data-placeholder'] ) && is_string( $args['data-placeholder'] ) && preg_match( '/(-?[0-9]+\.[0-9]+),(-?[0-9]+\.[0-9]+)/', $args['data-placeholder'], $match ) ) {
				$params = array(
					'latitude'  => $match[1],
					'longitude' => $match[2],
					'zoom'      => isset( $args['zoom'] ) ? $args['zoom'] : 0,
				);
			} else {
				$params['hidden'] = 'data-c-f-hidden=1 ';
			}
		} else {
			$params['zoom']   = isset( $args['zoom'] ) ? $args['zoom'] : 0;
			$params['hidden'] = '';
		}

		return '<iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" ' . $params['hidden'] . 'data-c-f-id="' . $args['data-id'] . '" src="https://maps.google.com/maps?q=' . $params['latitude'] . ',' . $params['longitude'] . '&amp;t=m&amp;z=' . $params['zoom'] . '&amp;output=embed&amp;iwloc=near"></iframe>';
	}

	public function render_custom_fields_audio( $args = array(), $param = null ) {

		$params = empty( $args['in_postlist'] ) ? $this->get_custom_fields_shortcode_params( $args['data-id'], 'audio' ) : $param;

		if ( empty( $params ) ) {

			$params = array_merge( $params, array(
				'mime_type' => 'audio/mp3',
				'id'        => '0',
				'width'     => '100',
				'height'    => '100',
				'title'     => 'Placeholder',
				'url'       => '',
				'name'      => $args['data-id'],
			) );

			if ( isset( $args['data-placeholder'] ) ) {
				$params['url'] = wp_get_attachment_url( $args['data-placeholder'] );
			}

			if ( ! is_editor_page_raw( true ) && ! isset( $args['data-placeholder'] ) ) {
				return '';
			}
		}

		$params['extra'] = '';
		$params['extra'] .= isset( $args[ 'loop' . '' ] ) && $args['loop'] === '1' ? 'loop="1"' : '';
		$params['extra'] .= isset( $args['no_download'] ) && $args['no_download'] === '1' ? 'controlslist="nodownload" ' : '';
		$params['extra'] .= isset( $args['autoplay'] ) && $args['autoplay'] === '1' ? 'data-autoplay="1" ' : '';
		$params['extra'] .= empty( $args['in_postlist'] ) ? '' : 'data-post-list="1" ';

		return tcb_template( 'custom-fields-elements/audio.phtml', $params, true );
	}

	private function get_video_params( $args, $params, $set_source = false ) {
		$params['value']    = empty( $params['value'] ) ? '' : $params['value'];
		$params['video_id'] = $this->verify_video_url( $params['value'], true );
		$args['data-type']  = $set_source ? $params['video_id'] : $args['data-type'];

		switch ( $args['data-type'] ) {
			case 'youtube':
				$params['video_id']    = empty( $params['video_id'] ) ? '' : $params['video_id'][1];
				$params['embeded_url'] = 'https://www.youtube.com/embed/' . $params['video_id'] . $args['data-query'];
				break;
			case 'vimeo':
				$params['video_id']    = empty( $params['video_id'] ) ? '' : $params['video_id'][4];
				$params['embeded_url'] = 'https://player.vimeo.com/video/' . $params['video_id'] . $args['data-query'];
				break;
			case 'wistia':
				$params['video_id']    = empty( $params['video_id'] ) ? '' : $params['video_id'][ count( $params['video_id'] ) - 1 ];
				$params['embeded_url'] = 'https://fast.wistia.net/embed/iframe/' . $params['video_id'] . $args['data-query'];
				break;
			case 'vooplayer':
				$params['video_id'] = empty( $params['video_id'] ) ? '' : $params['video_id'][3];
				break;
		}

		return $params;
	}

	public function render_custom_fields_video( $args = array(), $param = null ) {

		$params = empty( $args['in_postlist'] ) ? $this->get_custom_fields_shortcode_params( $args['data-id'], 'video' ) : $param;
		if ( ! empty( $params ) ) {
			if ( empty( $params['extra'] ) ) {
				$params['extra'] = '';
			}
			$params['value'] = empty( $params['value'] ) ? $params['url'] : $params['value'];
			$video_provider  = ( $this->verify_video_url( $params['value'] ) );
			//Update the mime type for external cf videos
			isset( $video_provider['mime_type'] ) && $params['mime_type'] = $video_provider['mime_type'];
			$params['extra'] .= empty( $args['in_postlist'] ) ? '' : ' data-post-list="1" ';
			$params['extra'] .= empty( $args['data-query'] ) ? '' : $args['data-query'];
		}
		/** Set placeholder data  for (empty links) and (videos with diff source in postlist)*/
		if ( empty( $params ) || ( ! empty( $args['data-type'] ) && $video_provider['video_src'] !== $args['data-type'] && ! empty( $args['in_postlist'] ) ) ) {
			$params                      = array_merge( $params, array(
				'mime_type' => 'video/mp4',
				'id'        => '0',
				'title'     => 'Placeholder',
				'url'       => '',
				'name'      => $args['data-id'],
			) );
			$video_provider['video_src'] = 'external';
			if ( isset( $args['data-placeholder'] ) ) {
				//Check if placeholder is of external type
				if ( is_numeric( $args['data-placeholder'] ) ) {
					$params['url']      = wp_get_attachment_url( $args['data-placeholder'] );
					$params['extra']    = 'controls="controls"';
					$args['data-query'] = '';
				} else {
					$params['value'] = $args['data-placeholder'];
					$params          = $this->get_video_params( $args, $params, true );
					$video_provider  = ( $this->verify_video_url( $params['value'] ) );
				}
			}

			if ( ! is_editor_page_raw( true ) && ! isset( $args['data-placeholder'] ) ) {
				return '';
			}
		}

		$template = tcb_template( 'custom-fields-elements/video.phtml', $params, true );

		$args['data-type'] = empty( $args['data-type'] ) ? 'external' : $video_provider['video_src'];
		switch ( $args['data-type'] ) {
			case 'youtube':
			case 'vimeo':
			case 'wistia':
				$params   = $this->get_video_params( $args, $params );
				$template = '<iframe class="tcb-responsive-video" data-code="' . $params['video_id'] . '" data-provider="' . $args['data-type'] . '" src="' . $params['embeded_url'] . '" data-src="' . $params['embeded_url'] . '" frameborder="0" allowfullscreen data-c-f-id="' . $args['data-id'] . '"' . $params['extra'] . '"></iframe>';
				break;
			case 'vooplayer':
				$params   = $this->get_video_params( $args, $params );
				$template = '<iframe allow="autoplay" data-code="' . $params['video_id'] . '" data-provider="' . $args['data-type'] . '" class="video-player-container vooplayer tcb-responsive-video" data-playerId="' . $params['video_id'] . '" url-params="" allowtransparency="true"  name="vooplayerframe" frameborder="0" allowfullscreen="true" scrolling="no" data-src="' . $params['value'] . '" src="" style="max-width: 100%; position:relative; opacity: 1; min-width: 100%; height:100% !important; width: auto; top: auto;" data-c-f-id="' . $args['data-id'] . '"' . $params['extra'] . '"></iframe>';
				break;
		}

		return $template;
	}

	public function render_custom_fields_image( $args = array(), $param ) {

		$html = '';

		$params = empty( $args['in_postlist'] ) ? $this->get_custom_fields_shortcode_params( $args['data-id'], 'image' ) : $param;

		if ( empty( $params ) && ! isset( $args['data-placeholder'] ) ) {

			$params = array(
				'alt'      => 'Placeholder',
				'id'       => '0',
				'width'    => '100',
				'height'   => '100',
				'title'    => 'Placeholder',
				'url'      => tve_editor_url( 'editor/css/images/featured_image.png' ),
				'name'     => $args['data-id'],
				'data-css' => isset( $args['data-css'] ) ? $args['data-css'] : '',
			);

			if ( ! is_editor_page_raw( true ) ) {
				return '';
			}

			$params['extra']   = empty( $args['in_postlist'] ) ? '' : 'data-post-list="1"';
			$params['classes'] = empty( $args['data-classes'] ) ? 'tve_image wp-image-' . $params['id'] . '' : $args['data-classes'];

			$html = tcb_template( 'custom-fields-elements/image.phtml', $params, true );

		} else {

			if ( empty( $params ) && isset( $args['data-placeholder'] ) ) {
				$params['id']    = $args['data-placeholder'];
				$params['alt']   = 'Placeholder';
				$params['name']  = $args['data-id'];
				$params['title'] = 'Placeholder';
			}
			$aux = array(
				'class'       => empty( $args['data-classes'] ) ? 'tve_image wp-image-' . $params['id'] . '' : $args['data-classes'],
				'alt'         => esc_attr( $params['alt'] ),
				'data-c-f-id' => esc_attr( $params['name'] ),
				'title'       => esc_attr( $params['title'] ),
			);

			if ( ! empty( $args['in_postlist'] ) ) {
				$aux['data-post-list'] = esc_attr( 1 );
			}
			if ( ! empty( $args['data-css'] ) ) {
				$aux['data-css'] = esc_attr( $args['data-css'] );
			}
			$html = wp_get_attachment_image(
				$params['id'],
				'full',
				false,
				$aux
			);
		}

		return $html;
	}

	public function render_custom_fields_countdown( $args = array(), $param = null ) {

		$params = empty( $args['in_postlist'] ) ? $this->get_custom_fields_shortcode_params( $args['data-id'], 'countdown' ) : $param;
		$return = array();

		if ( ! empty( $args['in_postlist'] ) ) {
			$return[] = array( 'prop' => 'data-post-list', 'value' => '1' );
		}

		if ( empty( $params ) ) {
			$params = array(
				'date' => '2020-01-01',
				'hour' => '00',
				'min'  => '00',
			);

			if ( isset( $args['data-placeholder'] ) ) {
				$aux    = explode( ':', $args['data-placeholder'] );
				$params = array(
					'date' => $aux[0],
					'hour' => $aux[1],
					'min'  => $aux[2],
				);
			}

			if ( ! is_editor_page_raw( true ) && ! isset( $args['data-placeholder'] ) ) {
				$return[] = array( 'prop' => 'data-c-f-hidden', 'value' => 1 );

				return htmlspecialchars( wp_json_encode( $return ), ENT_QUOTES );
			}
		}

		$return[] = array( 'prop' => 'data-date', 'value' => $params['date'] );
		$return[] = array( 'prop' => 'data-hour', 'value' => $params['hour'] );
		$return[] = array( 'prop' => 'data-min', 'value' => $params['min'] );

		return htmlspecialchars( wp_json_encode( $return ), ENT_QUOTES );
	}

	public function render_custom_fields_number( $args = array(), $param = null ) {

		$params = empty( $args['in_postlist'] ) ? $this->get_custom_fields_shortcode_params( $args['data-id'], 'number' ) : $param;
		$return = array();

		if ( ! empty( $args['in_postlist'] ) ) {
			$return[] = array( 'prop' => 'data-post-list', 'value' => '1' );
		}

		if ( empty( $params ) ) {
			$params = array(
				'value' => '0',
			);

			if ( isset( $args['data-placeholder'] ) ) {
				$params['value'] = $args['data-placeholder'];
			}

			if ( ! is_editor_page_raw( true ) && ! isset( $args['data-placeholder'] ) ) {
				$return[] = array( 'prop' => 'data-c-f-hidden', 'value' => 1 );

				return htmlspecialchars( wp_json_encode( $return ), ENT_QUOTES );
			}
		}

		if ( ! empty( $args['field-validation'] ) && ! empty( $args['data-attribute'] ) ) {

			switch ( $args['field-validation'] ) {
				case 'percentage':
					if ( ! is_numeric( $params['value'] ) || $params['value'] < 0 ) {
						$params['value'] = 0;
					}
					break;
				case 'rating':
					if ( ! is_numeric( $params['value'] ) || $params['value'] < 0 || fmod( $params['value'], 0.5 ) !== 0.0 ) {
						$params['value'] = 0;
					}
					break;
				default:
					break;
			}
			$return[] = array( 'prop' => $args['data-attribute'], 'value' => $params['value'] );
		}

		return htmlspecialchars( wp_json_encode( $return ), ENT_QUOTES );
	}

	/**
	 * Filter allowed shortcodes for tve_do_wp_shortcodes
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function allowed_shortcodes( $shortcodes ) {
		return array_merge( $shortcodes, array(
			static::GLOBAL_SHORTCODE_URL,
			static::GLOBAL_SHORTCODE_DATA,
			'tcb_custom_field',
			'tcb_dynamic_field',
		), array() );
	}

	/**
	 * Renders the user shortcodes
	 *
	 * @param $attr
	 * @param $content
	 * @param $tag
	 *
	 * @return void|string
	 */
	public function render_user_shortcode( $attr, $content, $tag ) {
		if ( ! is_editor_page_raw( true ) ) {
			$current_user = wp_get_current_user();

			if ( ! empty( $current_user->ID ) ) {
				$prop = $this->user_shortcodes[ $tag ]['property'];

				$return = $current_user->$prop;

				if ( ! empty( $attr['link_to_profile'] ) ) {
					$return = sprintf( '<a href="%s" target="_blank">%s</a>', get_edit_profile_url( $current_user->ID ), $return );
				}
			} else {
				$return = $attr['text_not_logged'];
			}

			return $return;
		}
	}

	/**
	 * Add global shortcodes to be used in dynamic links
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function global_links_shortcodes( $links ) {
		$global_links = array_values( $this->global_custom_links( get_the_ID() ) );

		if ( ! empty( $global_links ) ) {
			$links['Custom Fields Global'] = array(
				'links'     => array( $global_links ),
				'shortcode' => static::GLOBAL_SHORTCODE_URL,
			);
		}

		return $links;
	}

	/**
	 * Global data related to the custom fields with links
	 *
	 * Gets all the custom fields for the current post or post with given id, selects only the fields that correspond to a http link
	 * and returns an array of objects with the structure of a dynamic link
	 *
	 * @param null $post_id
	 *
	 * @return array
	 */
	public function global_custom_links( $post_id = null ) {
		$post_id = $post_id === null ? get_the_ID() : intval( $post_id );
		$custom  = get_post_custom( $post_id );
		//Get all the keys that are not protected meta and are links
		$custom_keys = array_filter( ( array ) get_post_custom_keys( $post_id ), function ( $meta ) use ( $custom ) {
			return apply_filters( 'is_protected_meta', ! filter_var( $custom[ $meta ][0], FILTER_VALIDATE_URL ), $meta, null ) === false;
		} );

		$items = array();
		foreach ( $custom_keys as $val ) {
			$items[ $val ] = array(
				'name'  => $val,
				'label' => $val,
				'url'   => $custom[ $val ][0],
				'show'  => true,
				'id'    => $post_id . '::' . $val,
			);
		}

		if ( ! isset( $this->external_fields ) ) {
			$this->external_fields = $this->get_all_external_fields();
		}
		if ( ! empty( $this->external_fields['link'] ) ) {
			foreach ( $this->external_fields['link'] as $key => $val ) {
				$items[ $key ] = array(
					'name'  => $val['name'],
					'label' => $val['label'],
					'url'   => isset( $val['value']['url'] ) ? $val['value']['url'] : $val['value'],
					'show'  => true,
					'id'    => $post_id . '::' . $key,
				);
			}
		}

		return $items;
	}

	/**
	 * Global data related to the custom fields
	 *
	 * Gets all the custom fields for the current post or post with given id, selects only the fields that do not correspond to a http link
	 * and returns an array of objects with the structure of an inline shortcode
	 *
	 * @param null $post_id
	 *
	 * @return array
	 */
	public function global_custom_metadata( $post_id = null ) {
		$post_id = $post_id === null ? get_the_ID() : intval( $post_id );
		$custom  = get_post_custom( $post_id );
		//Get all the keys that are not protected meta and not links
		$custom_keys = array_filter( (array) get_post_custom_keys( $post_id ), function ( $meta ) use ( $custom ) {
			return apply_filters( 'is_protected_meta', filter_var( $custom[ $meta ][0], FILTER_VALIDATE_URL ), $meta, null ) === false;
		} );

		$real_data  = array();
		$value      = array();
		$value_type = array();
		$labels     = array();

		foreach ( $custom_keys as $val ) {
			$key                = $post_id . '::' . $val;
			$real_data[ $key ]  = $custom[ $val ][0]; //Value that will be displayed
			$value[ $key ]      = $val;               //Value appearing as option title
			$value_type[ $key ] = '0';                //Value type (text)

			$labels[ $val ] = static::get_label_for_key( $val, $post_id );
		}

		if ( ! isset( $this->external_fields ) ) {
			$this->external_fields = $this->get_all_external_fields();
		}

		if ( ! empty( $this->external_fields['text'] ) ) {
			foreach ( $this->external_fields['text'] as $k => $v ) {
				$key                = $post_id . '::' . $k;
				$real_data[ $key ]  = $v['value']; //Value that will be displayed
				$value[ $key ]      = $v['label'];               //Value appearing as option title
				$value_type[ $key ] = '0';                //Value type (text)

				$labels[ $k ] = $v['label'];
			}
		}

		return array(
			'real_data'  => $real_data,
			'value'      => $value,
			'value_type' => $value_type,
			'labels'     => $labels,
		);
	}

	/**
	 * Get the 'nice' display name for this custom field key.
	 * Each CF plugin seems to have its own way of retrieving these
	 *
	 * Used also in our Theme Builder
	 *
	 * @param $key
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_label_for_key( $key, $post_id ) {
		$label = '';

		$field_obj = static::get_post_acf_data( $key, $post_id );

		if ( ! empty( $field_obj ) && ! empty( $field_obj['label'] ) ) {
			$label = $field_obj['label'];
		}

		return $label;
	}

	/**
	 * Check if this post + key have ACF data. If it does, return it, else return an empty array
	 * Used in TTB
	 *
	 * @param $key
	 * @param $post_id
	 *
	 * @return array
	 */
	public static function get_post_acf_data( $key, $post_id ) {
		$field_obj = array();

		if ( function_exists( 'get_field_object' ) ) {
			$field_obj = get_field_object( $key, $post_id );
		}

		return $field_obj;
	}

	/**
	 * Add some inline shortcodes.
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function tcb_inline_shortcodes( $shortcodes ) {

		$custom_data_global = $this->global_custom_metadata();

		$custom_shortcodes = array(
			'Custom fields' => array(
				array(
					'name'        => __( 'Custom Fields Global', 'thrive-cb' ),
					'value'       => static::GLOBAL_SHORTCODE_DATA,
					'option'      => __( 'Custom Fields', 'thrive-cb' ),
					'extra_param' => 'CFG',
					'input'       => array(
						'id' => array(
							'extra_options' => array(),
							'label'         => 'Field',
							'real_data'     => $custom_data_global['real_data'],
							'type'          => 'select',
							'value'         => $custom_data_global['value'],
							'value_type'    => $custom_data_global['value_type'],
							'labels'        => $custom_data_global['labels'],
						),
					),
				),
				array(
					'name'        => 'Custom Fields Postlist',
					'value'       => 'tcb_post_custom_field',
					'option'      => __( 'Custom Fields', 'thrive-cb' ),
					'extra_param' => 'CFP',
					'input'       => array(
						'id' => array(
							'extra_options' => array(),
							'label'         => 'Field',
							'real_data'     => array(),
							'type'          => 'select',
							'value'         => array(),
							'value_type'    => array(),
							'labels'        => array(),
						),
					),
				),
			),
		);

		return array_merge_recursive( $shortcodes, $custom_shortcodes );

	}

	/**
	 * Replace the shortcode with its content
	 *
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public function global_shortcode_url_link( $args ) {
		$data = '';

		if ( ! empty( $args['id'] ) ) {
			$shortcode_data = $this->get_parsed_shortcode_data( $args['id'] );

			$groups = $this->global_custom_links( (int) $shortcode_data['post_id'] );

			if ( isset( $groups[ $shortcode_data['id'] ] ) ) {
				$data = $groups[ $shortcode_data['id'] ]['url'];
			}
		}

		return $data;
	}

	/**
	 * Replace the shortcode with its content
	 *
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public function global_shortcode_url_data( $args ) {
		$data = '';

		if ( ! empty( $args['id'] ) ) {
			$shortcode_data = $this->get_parsed_shortcode_data( $args['id'] );

			$full_id = $shortcode_data['post_id'] . '::' . $shortcode_data['id'];
			$groups  = $this->global_custom_metadata( (int) $shortcode_data['post_id'] );

			if ( isset( $groups['real_data'][ $full_id ] ) ) {
				$data = $groups['real_data'][ $full_id ];
			}
		}

		return $data;
	}

	/**
	 * Get the post ID and the shortcode ID from the string. If no post ID exists, use the current post ID.
	 *
	 * @param $data
	 *
	 * @return array
	 */
	private function get_parsed_shortcode_data( $data ) {
		if ( strpos( $data, '::' ) ) {
			$shortcode_data = explode( '::', $data );

			$post_id      = $shortcode_data[0];
			$shortcode_id = $shortcode_data[1];
		} else {
			/* in certain cases where we don't have an ID ( TTB ), get the current post ID */
			$post_id      = get_the_ID();
			$shortcode_id = $data;
		}

		return array(
			'post_id' => $post_id,
			'id'      => $shortcode_id,
		);
	}

	private function verify_video_url( $url, $get_regex = false ) {

		foreach ( $this->video_regex as $reg => $provider ) {
			if ( preg_match( $reg, $url, $aux ) ) {
				return $get_regex ? $aux : array(
					'value'     => $aux[0],
					'video_src' => $provider,
				);
			}
		}

		if ( preg_match( '/.+\.(wmv|avi|mov|mpg|mp4|m4v|ogv|3gp|3g2|webm)$/', $url, $aux ) ) {
			//Exception for mov file types
			if ( $aux[1] == 'mov' ) {
				$aux[1] = 'mp4';
			}

			return $get_regex ? $aux : array(
				'value'     => $url,
				'video_src' => 'external',
				'url'       => $url,
				'title'     => 'External Video',
				'id'        => 0,
				'mime_type' => 'video/' . $aux[1],
			);
		}

		return false;
	}

	/**
	 * Integration with Advanced Custom Fields
	 * https://www.advancedcustomfields.com/
	 *
	 * Returns an array of custom fields from the plugin
	 *
	 * @param $post_id
	 * @param $field_types
	 *
	 * @return array
	 */
	private function get_acf_fields( $post_id = false, $field_types = array() ) {

		$advanced_custom_fields = get_field_objects( $post_id, false );
		$fields                 = array();

		if ( ! is_array( $advanced_custom_fields ) ) {
			return $fields;
		}

		foreach ( $advanced_custom_fields as $field_key => $value ) {
			$attachment      = acf_get_attachment( $value['value'] );
			$formatted_value = get_field( $value['key'] );

			$acf_key = static::ACF_PREFIX . $field_key;

			if ( $value['value'] === '' && 'true_false' !== $value['type'] ) {
				continue;
			}

			foreach ( $field_types as $k => $v ) {

				if ( ! in_array( $value['type'], $v ) ) {
					continue;
				}

				if ( ! isset( $fields[ $k ] ) ) {
					$fields[ $k ] = array();
				}

				if ( ! isset( $fields[ $k ][ $acf_key ] ) ) {

					$field = array();

					switch ( $k ) {
						case 'countdown':
							$field = array( 'value' => $formatted_value );

							$date          = date_create_from_format( 'Y-m-d H:i:s', $value['value'] );
							$field['date'] = $date->format( 'Y-m-d' );
							$field['hour'] = $date->format( 'H' );
							$field['min']  = $date->format( 'i' );

							break;
						case 'link':
							$field = array( 'value' => $formatted_value );
							if ( in_array( $value['type'], array( 'file', 'image' ) ) ) {
								$field['value'] = $attachment['url'];
							} else {
								$field['value'] = $value['type'] === 'page_link' ? get_permalink( $value['value'] )
									: ( $value['type'] === 'link' ? $value['value']['url'] : $field['value'] );
							}

							if ( ! filter_var( $field['value'], FILTER_VALIDATE_URL ) ) {
								if ( filter_var( $field['value'], FILTER_VALIDATE_EMAIL ) ) {
									$field['value'] = 'mailto:' . $field['value'];
								} else {
									$field = null;
								}
							}

							break;
						case 'video':
						case 'audio':
						case 'image':
							//TODO Filter types in case of video/audio (ex: .flv is not working)
							if ( ! empty( $attachment ) && $attachment['type'] === $k ) {
								$field              = array_merge( $attachment, array(
									'name' => $acf_key,
									'mime' => $attachment['mime_type'],
								) );
								$field['video_src'] = 'external';
							} else {
								$field = $k === 'video' ? $this->verify_video_url( $value['value'] ) : false;
							}

							break;
						case 'map':
							if ( is_string( $value['value'] ) && preg_match( '/(-?[0-9]+\.[0-9]+),(-?[0-9]+\.[0-9]+)/', $value['value'], $match ) ) {
								$field = array(
									'latitude'  => $match[1],
									'longitude' => $match[2],
								);
							}

							break;
						default:
							$aux = get_field_object( $field_key );
							if ( ! empty( $aux['display_format'] ) ) {     //Render Date, Date-Time, Time in display format selected by user
								$field = array( 'value' => date_format( DateTime::createFromFormat( $aux['return_format'], $aux['value'] ), $aux['display_format'] ) );
							} else if ( $value['type'] === 'checkbox' ) {  //Convert from Checkbox array to string
								$field = array( 'value' => implode( ', ', $value['value'] ) );
							} else {
								$value['value'] = preg_replace( '(\n)', '<br>', $value['value'] ); //replace \n from text/textarea
								$field          = array( 'value' => $value['value'] );
							}
							break;
					}

					if ( ! empty( $field ) && is_array( $field ) ) {
						$acf_key                  = $this->replace_characters( $acf_key );
						$fields[ $k ][ $acf_key ] = array_merge( array(
							'label' => $value['label'],
							'name'  => $acf_key,
							'type'  => $value['type'],
						), $field );
					}
				}
			}
		}

		return $fields;
	}

	public function replace_characters( $str ) {

		foreach ( $this->pattern_replacement as $pattern => $replacement ) {
			$str = preg_replace( $pattern, $replacement, $str );
		}

		return $str;
	}

	/**
	 * Entry point for retrieving all custom fields
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function get_all_external_fields() {

		if ( ! empty( $this->external_fields ) ) {
			return $this->external_fields;
		}

		$this->external_fields = array();
		if ( $this->has_external_fields_plugins() ) {
			$this->external_fields = array_merge_recursive( $this->get_acf_fields( false, $this->field_types ), $this->external_fields );
		}

		return $this->external_fields;
	}

	/**
	 * Fetches the fields that are available for postlist element for post with given post_id
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_all_external_postlist_fields( $post_id ) {

		$fields = array();

		if ( $this->has_external_fields_plugins() ) {
			$fields = $this->get_acf_fields( $post_id, $this->postlist_field_types );
		}

		return $fields;
	}
}

/**
 * Returns the instance of the Custom Fields Shortcode Class
 *
 * @return TCB_Custom_Fields_Shortcode
 */
function tcb_custom_fields_api() {
	return TCB_Custom_Fields_Shortcode::get_instance();
}

tcb_custom_fields_api();
