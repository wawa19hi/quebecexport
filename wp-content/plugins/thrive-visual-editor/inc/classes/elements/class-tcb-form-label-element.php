<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/15/2018
 * Time: 3:51 PM
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Form_Label_Element extends TCB_Element_Abstract {

	/**
	 * Name of the Element
	 *
	 * @return string
	 */
	public function name() {

		return __( 'Form Label', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-form-item label';
	}

	/**
	 * There is no need for HTML for this element since we need it only for control filter
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Hide Element From Sidebar Menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}


	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'typography'       => array(
				'disabled_controls' => array( 'TextAlign', '.tve-advanced-controls' ),
				'config'            => array(
					'css_suffix'    => '',
					'FontSize'      => array(
						'css_suffix' => '',
						'important'  => true,
					),
					'FontColor'     => array(
						'css_suffix' => '',
						'important'  => true,
					),
					'LineHeight'    => array(
						'css_suffix' => '',
						'important'  => true,
					),
					'LetterSpacing' => array(
						'css_suffix' => '',
						'important'  => true,
					),
					'FontFace'      => array(
						'css_suffix' => '',
						'important'  => true,
					),
					'TextStyle'     => array(
						'css_suffix' => '',
						'important'  => true,
					),
				),
			),
			'animation'        => array(
				'hidden' => true,
			),
			'responsive'       => array(
				'hidden' => true,
			),
			'styles-templates' => array(
				'hidden' => true,
			),
		);
	}
}
