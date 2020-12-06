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
 * Class TCB_Image_Element
 */
class TCB_Image_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Image', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'media';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'image';
	}

	/**
	 * Text element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return 'div.tve_image_caption';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'image'         => array(
				'config' => array(
					'StyleChange'    => array(
						'config' => array(
							'label' => __( 'Image Style', 'thrive-cb' ),
						),
					),
					'ImagePicker'    => array(
						'config' => array(
							'label' => __( 'Replace Image', 'thrive-cb' ),
						),
					),
					'ExternalFields' => array(
						'config'  => array(
							'main_dropdown'     => array(
								''         => __( 'Select A Source', 'thrive-cb' ),
								'featured' => __( 'Featured Image', 'thrive-cb' ),
								'author'   => __( 'Author Image', 'thrive-cb' ),
								'custom'   => __( 'Custom Fields', 'thrive-cb' ),
							),
							'key'               => 'image',
							'shortcode_element' => 'img.tve_image',
						),
						'extends' => 'CustomFields',
					),
					'ImageSize'      => array(
						'config'  => array(
							'default'  => 'auto',
							'min'      => '30',
							'forceMin' => '5',
							'max'      => '1024',
							'label'    => __( 'Size', 'thrive-cb' ),
							'um'       => array( 'px', '%' ),
							'css'      => 'width',
						),
						'extends' => 'Slider',
					),
					'ImageHeight'    => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '5',
							'max'     => '300',
							'label'   => __( 'Height', 'thrive-cb' ),
							'um'      => array( '' ),
							'css'     => 'height',
						),
						'extends' => 'Slider',
					),
					'StylePicker'    => array(
						'config' => array(
							'label'   => __( 'Choose image style', 'thrive-cb' ),
							'items'   => array(
								'no_style'                  => __( 'No Style', 'thrive-cb' ),
								'img_style_dark_frame'      => __( 'Dark Frame', 'thrive-cb' ),
								'img_style_framed'          => __( 'Framed', 'thrive-cb' ),
								'img_style_lifted_style1'   => __( 'Lifted Style 1', 'thrive-cb' ),
								'img_style_lifted_style2'   => __( 'Lifted Style 2', 'thrive-cb' ),
								'img_style_polaroid'        => __( 'Polaroid', 'thrive-cb' ),
								'img_style_rounded_corners' => __( 'Rounded Corners', 'thrive-cb' ),
								'img_style_circle'          => __( 'Circle', 'thrive-cb' ),
								'img_style_caption_overlay' => __( 'Caption Overlay', 'thrive-cb' ),
							),
							'default' => 'no_style',
						),
					),
					'ImageTitle'     => array(
						'config'  => array(
							'label' => __( 'Title', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'ImageAltText'   => array(
						'config'  => array(
							'label' => __( 'Alt Text', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'ImageCaption'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Add caption text', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'ImageFullSize'  => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Open full size image on click', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
				),
			),
			'background'    => array( 'hidden' => true ),
			'image-effects' => array(
				'config' => array(
					'css_suffix'         => ' img',
					'ImageGreyscale'     => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Greyscale', 'thrive-cb' ),
							'um'      => array( '' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageOpacity'       => array(
						'config'  => array(
							'default' => '100',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Opacity', 'thrive-cb' ),
							'um'      => array( '' ),
							'css'     => 'opacity',
						),
						'extends' => 'Slider',
					),
					'ImageBlur'          => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '20',
							'label'   => __( 'Blur', 'thrive-cb' ),
							'um'      => array( 'px' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageBrightness'    => array(
						'config'  => array(
							'default' => '100',
							'min'     => '0',
							'max'     => '300',
							'label'   => __( 'Brightness', 'thrive-cb' ),
							'um'      => array( '' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageContrast'      => array(
						'config'  => array(
							'default' => '100',
							'min'     => '0',
							'max'     => '300',
							'label'   => __( 'Contrast', 'thrive-cb' ),
							'um'      => array( '' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageSepia'         => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Sepia', 'thrive-cb' ),
							'um'      => array( '' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageInvert'        => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Invert', 'thrive-cb' ),
							'um'      => array( '' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageSaturate'      => array(
						'config'  => array(
							'default' => '100',
							'min'     => '0',
							'max'     => '300',
							'label'   => __( 'Saturate', 'thrive-cb' ),
							'um'      => array( '' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageHueRotate'     => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '359',
							'label'   => __( 'Hue Rotate', 'thrive-cb' ),
							'um'      => array( 'deg' ),
							'css'     => 'filter',
						),
						'extends' => 'Knob',
					),
					'ImageOverlaySwitch' => array(
						'strategy' => 'element',
						'config'   => array(
							'name'    => '',
							'label'   => __( 'Image Overlay', 'thrive-cb' ),
							'default' => true,
						),
						'extends'  => 'Switch',
					),
					'ImageOverlay'       => array(
						'config'     => array(
							'default' => '000',
							'label'   => __( 'Overlay Color', 'thrive-cb' ),
						),
						'css_suffix' => ' .tve-image-overlay',
						'extends'    => 'ColorPicker',
					),
				),
			),
			'typography'    => array(
				'hidden' => true,
			),
			'animation'     => array(
				'config' => array(
					'to' => 'img',
				),
			),
			'layout'        => array(
				'disabled_controls' => array(
					'Width',
					'Height',
					'Overflow',
				),
			),
			'shadow'        => array(
				'config' => array(
					'disabled_controls' => array( 'inner', 'text' ),
				),
			),
			'scroll'        => array(
				'hidden' => false,
			),
		);
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return self::get_thrive_basic_label();
	}
}
