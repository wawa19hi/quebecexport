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
 * Class TCB_Countdown_Label_Element
 */
class TCB_Countdown_Label_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Countdown Label', 'thrive-cb' );
	}


	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-countdown-label';
	}


	public function hide() {
		return true;
	}

	public function own_components() {

		return array(
			'text'       => array(
				'disabled_controls' => array( '.tve-advanced-controls', ),
				'config'            => array(
					'FontSize'       => array(
						'config'  => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Font Size', 'thrive-cb' ),
							'um'      => array( 'px', 'em' ),
							'css'     => 'fontSize',
						),
						'extends' => 'Slider',
					),
					'TextStyle'      => array(
						'css_prefix' => tcb_selection_root() . ' ',
						'config'     => array(
							'important' => true,
						),
					),
					'LineHeight'     => array(
						'config'  => array(
							'default' => '1',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Line Height', 'thrive-cb' ),
							'um'      => array( 'em', 'px' ),
							'css'     => 'lineHeight',
						),
						'extends' => 'Slider',
					),
					'LetterSpacing'  => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Letter Spacing', 'thrive-cb' ),
							'um'      => array( 'px' ),
							'css'     => 'letterSpacing',
						),
						'extends' => 'Slider',
					),
					'FontColor'      => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Font Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'FontBackground' => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Font Highlight', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'FontFace'       => array(
						'config'  => array(
							'template' => 'controls/font-manager',
							'inline'   => true,
						),
						'extends' => 'FontManager',
					),
				),
			),
			'typography' => array( 'hidden' => true ),
			'animation'  => array(
				'hidden' => true,
			),
			'layout'     => array(
				'disabled_controls' => array( 'Display', 'Alignment', '.tve-advanced-controls', 'Height' ),
			),
		);
	}
}
