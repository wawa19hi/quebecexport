<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Thumbnail_Element
 */
class TCB_Post_Thumbnail_Element extends TCB_Post_List_Sub_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Featured Image', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'featured-image';
	}

	/**
	 * Wordpress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_POST_THUMBNAIL_IDENTIFIER;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public function shortcode() {
		return 'tcb_post_featured_image';
	}

	/**
	 * Element HTML
	 *
	 * @return string
	 */
	public function html() {
		return TCB_Utils::wrap_content( '', 'a', '', TCB_POST_THUMBNAIL_IDENTIFIER . ' ' . THRIVE_WRAPPER_CLASS . ' ' . TCB_SHORTCODE_CLASS );
	}

	/**
	 * Default components that most theme elements use
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'animation'        => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'shadow'           => array(
				'config' => array(
					/* only the drop-shadow makes sense for images, disable the rest */
					'disabled_controls' => array( 'inner', 'text' ),
					/* apply shadows on the image and not on the container */
					'css_suffix'        => ' img',
				),
			),
			'borders'          => array(
				'config' => array(
					/* apply borders on the image and not on the container */
					'css_suffix' => ' img',
				),
			),
			'typography'       => array( 'hidden' => true ),
			'background'       => array( 'hidden' => true ),
			'post_thumbnail'   => array(
				'config' => array(
					'type_url'     => array(
						'config'  => array(
							'name'       => __( 'Featured Image URL', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'value' => 'none',
									'text'  => __( 'None', 'thrive-cb' ),
								),
								array(
									'value'   => 'post_url',
									'text'    => __( 'Post URL', 'thrive-cb' ),
									'default' => true,
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'type_display' => array(
						'config'  => array(
							'name'       => __( 'If the post has no featured image, display:', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'value' => 'nothing',
									'text'  => __( 'Nothing', 'thrive-cb' ),
								),
								array(
									'value'   => 'default_image',
									'text'    => __( 'Default image', 'thrive-cb' ),
									'default' => true,
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'size'         => array(
						'config'  => array(
							'default'     => 'thumbnail',
							'name'        => __( 'Image Size', 'thrive-cb' ),
							'options'     => self::get_size_options(),
						),
						'extends' => 'Select',
					),
					'ImageSize'    => array(
						'config' => array(
							'default' => 'auto',
							'min'     => '20',
							'max'     => '1024',
							'label'   => __( 'Size', 'thrive-cb' ),
							'um'      => array( 'px', '%' ),
							'css'     => 'width',
						),
					),
				),
			),
		);
	}

	/**
	 * Return possible image sizes
	 *
	 * @return array
	 */
	public static function get_size_options() {
		$labels = array();

		$sizes = TCB_Post_List_Featured_Image::filter_available_sizes();

		foreach ( $sizes as $key => $size ) {
			$labels[] = array(
				'name'  => $size,
				'value' => $key,
			);
		}

		return $labels;
	}

	/**
	 * The post categories should have hover state.
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}
