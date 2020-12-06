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
 * Class TCB_ContentBox_Element
 */
class TCB_ContentBox_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Content Box', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'container,box,content';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'content_box';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_contentbox_shortcode, .thrv-content-box:not(.tve_lg_file)';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix_config = array( 'css_prefix' => tcb_selection_root( false ) . ' .thrv-content-box ' );

		$content_box = array(
			'contentbox' => array(
				'config' => array(
					'ContentPalettes'      => array(
						'config'  => array(),
						'extends' => 'Palettes',
					),
					'BoxHeight'        => array(
						'config'  => array(
							'default' => '80',
							'min'     => '1',
							'max'     => '1000',
							'label'   => __( 'Minimum Height', 'thrive-cb' ),
							'um'      => array( 'px', 'vh' ),
							'css'     => 'min-height',
						),
						'to'      => ' > .tve-cb',
						'extends' => 'Slider',
					),
					'BoxWidth'         => array(
						'config'  => array(
							'default' => '1024',
							'min'     => '100',
							'max'     => '2000',
							'label'   => __( 'Maximum Width', 'thrive-cb' ),
							'um'      => array( 'px', '%' ),
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'MasterColor'      => array(
						'config' => array(
							'default'             => '000',
							'label'               => __( 'Master Color', 'thrive-cb' ),
							'important'           => true,
							'affected_components' => array( 'shadow', 'background', 'borders' ),
							'options'             => array(
								'showGlobals' => false,
							),
						),
					),
					'VerticalPosition' => array(
						'config'  => array(
							'name'    => __( 'Vertical Position', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => 'top',
									'default' => true,
									'value'   => '',
								),
								array(
									'icon'  => 'vertical',
									'value' => 'center',
								),
								array(
									'icon'  => 'bot',
									'value' => 'flex-end',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'style'            => array(
						'config' => array(
							'to'            => '> .tve-content-box-background',
							'label'         => __( 'Content Box Styles', 'thrive-cb' ),
							'items'         => array(
								'cb_style_1'  => __( 'Content box 1', 'thrive-cb' ),
								'cb_style_2'  => __( 'Content box 2', 'thrive-cb' ),
								'cb_style_3'  => __( 'Content box 3', 'thrive-cb' ),
								'cb_style_4'  => __( 'Content box 4', 'thrive-cb' ),
								'cb_style_5'  => __( 'Content box 5', 'thrive-cb' ),
								'cb_style_6'  => __( 'Content box 6', 'thrive-cb' ),
								'cb_style_7'  => __( 'Content box 7', 'thrive-cb' ),
								'cb_style_8'  => __( 'Content box 8', 'thrive-cb' ),
								'cb_style_9'  => __( 'Content box 9', 'thrive-cb' ),
								'cb_style_10' => __( 'Content box 10', 'thrive-cb' ),
								'cb_style_11' => __( 'Content box 11', 'thrive-cb' ),
								'cb_style_12' => __( 'Content box 12', 'thrive-cb' ),
								'cb_style_13' => __( 'Content box 13', 'thrive-cb' ),
								'cb_style_14' => __( 'Content box 14', 'thrive-cb' ),
								'cb_style_15' => __( 'Content box 15', 'thrive-cb' ),
							),
							'default_label' => __( 'Template ContentBox', 'thrive-cb' ),
							'default'       => 'default',
						),
					),
				),
			),
			'borders'    => array(
				'config' => array(
					'Borders' => array(
						'to'        => '>.tve-content-box-background',
						'important' => true,
					),
					'Corners' => array(
						'to' => '>.tve-content-box-background',
					),
				),
			),
			'layout'     => array(
				'config' => array(
					'Position' => array(
						'important'          => true,
						'disabled_positions' => array( 'auto' ),
					),
					'Height'   => array(
						'to'        => '> .tve-cb',
						'important' => true,
					),
				),
			),
			'background' => array(
				'config' => array(
					'to' => '>.tve-content-box-background',
				),
			),
			'shadow'     => array(
				'config' => array(
					'to' => '>.tve-content-box-background',
				),
			),
			'decoration' => array(
				'config' => array(
					'to' => '>.tve-content-box-background',
				),
			),
			'typography' => array(
				'disabled_controls' => array(),
				'config'            => array(
					'to'             => '> .tve-cb',
					'FontSize'       => $prefix_config,
					'FontColor'      => $prefix_config,
					'LineHeight'     => $prefix_config,
					'FontFace'       => $prefix_config,
					'ParagraphStyle' => array( 'hidden' => false ),
				),
			),
			'scroll'     => array(
				'hidden' => false,
			),
		);

		return array_merge( $content_box, $this->shared_styles_component(), $this->group_component() );
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
