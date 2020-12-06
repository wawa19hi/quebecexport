<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/22/2018
 * Time: 8:45 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Contact_Form_Input_Element extends TCB_Element_Abstract {

	/**
	 * Name of the Element
	 *
	 * @return string
	 */
	public function name() {

		return __( 'Contact Form Input', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-cf-input';
	}

	/**
	 * Enables Hover States on Form Input
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
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
	 * Component and control configda
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix_config           = tcb_selection_root();
		$controls_default_config = array(
			'css_suffix' => array(
				' input',
				' input::placeholder',
				' textarea',
				' textarea::placeholder',
			),
		);

		$tag_default_config = array(
			'css_suffix' => array(
				' input',
				' textarea',
			),
		);

		return array(
			'typography'       => array(
				'config' => array(
					'FontSize'      => $controls_default_config,
					'FontColor'     => $controls_default_config,
					'TextAlign'     => $controls_default_config,
					'TextStyle'     => $controls_default_config,
					'TextTransform' => $controls_default_config,
					'FontFace'      => $controls_default_config,
					'LineHeight'    => $controls_default_config,
					'LetterSpacing' => $controls_default_config,
				),
			),
			'layout'           => array(
				'disabled_controls' => array(
					'Width',
					'Height',
					'Alignment',
					'Display',
					'.tve-advanced-controls',
				),
				'config'            => array(
					'MarginAndPadding' => $tag_default_config,
				),
			),
			'borders'          => array(
				'config' => array(
					'Borders' => array_merge( array( 'css_prefix' => $prefix_config . ' ' ), $tag_default_config ),
					'Corners' => array_merge( array( 'css_prefix' => $prefix_config . ' ' ), $tag_default_config ),
				),
			),
			'animation'        => array(
				'hidden' => true,
			),
			'background'       => array(
				'config' => array(
					'ColorPicker' => $tag_default_config,
					'PreviewList' => $tag_default_config,
				),
			),
			'shadow'           => array(
				'config' => $tag_default_config,
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
