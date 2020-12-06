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
 * Class TCB_Countdown_Separator_Element
 */
class TCB_Countdown_Separator_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Countdown Separator', 'thrive-cb' );
	}


	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-countdown-separator';
	}


	public function hide() {
		return true;
	}

	public function own_components() {
		return array(
			'countdown_separator' => array(
				'config' => array(
					'Separator' => array(
						'config'  => array(
							'label' => 'Tile separator',
						),
						'extends' => 'LabelInput',
					),
				),
			),
			'typography'          => array(
				'disabled_controls' => array( '.tve-advanced-controls', '[data-value="tcb-typography-letter-spacing"]', 'TextAlign', 'TextTransform', 'FontBackground' ),
				'config'            => array(
					'FontSize'   => array(
						'css_suffix' => ' .tcb-plain-text',
					),
					'LineHeight' => array(
						'css_suffix' => ' .tcb-plain-text',
					),
				),
			),
			'layout'              => array(
				'disabled_controls' => array( 'Display', 'Alignment', '.tve-advanced-controls', 'Height' ),
				'config'            => array(
					'MarginAndPadding' => array(
						'padding_suffix' => ' span',
						'margin_suffix'  => '',
						'css_prefix'     => tcb_selection_root() . ' ',
						'important'      => true,
					),
				),
			),
			'responsive'          => array(
				'hidden' => true,
			),
			'animation'           => array(
				'hidden' => true,
			),
		);
	}
}
