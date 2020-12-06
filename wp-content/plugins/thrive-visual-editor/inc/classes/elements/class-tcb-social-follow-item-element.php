<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Social_Follow_Item_Element
 */
class TCB_Social_Follow_Item_Element extends TCB_Icon_Element {
	/**
	 * Element name
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Social Share Button', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_s_item';
	}

	/**
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['social_follow_item'] = array(
			'config' => array(
				'NetworkColor' => array(
					'config'  => array(
						'label' => __( 'Network Color', 'thrive-cb' ),
					),
					'extends' => 'ColorPicker',
				),
				'ColorPicker'  => array(
					'css_prefix' => tcb_selection_root() . ' ',
					'css_suffix' => array( ' svg.tcb-icon', ' .tve_s_icon .tve_sc_icon' ),
					'config'     => array(
						'label'   => __( 'Color', 'thrive-cb' ),
						'options' => array( 'noBeforeInit' => false ),
					),
				),
				'Slider'       => array(
					'css_suffix' => '.tve_s_item .tve_s_icon ',
					'config'     => array(
						'default' => '18',
						'min'     => '1',
						'max'     => '60',
						'label'   => __( 'Size', 'thrive-cb' ),
						'um'      => array( 'px' ),
						'css'     => 'fontSize',
					),
				),
			),
		);

		$components['scroll'] = array( 'hidden' => true );

		$components['borders']                     = array(
			'config' => array(
				'Borders' => array( 'css_prefix' => '.tve_social_items .tve_s_item', 'important' => 'true' ),
				'Corners' => array( 'css_prefix' => '.tve_social_items .tve_s_item', 'important' => 'true' ),
			),
		);
		$components['background']['config']        = array(
			'ColorPicker' => array( 'css_prefix' => tcb_selection_root() . ' .thrv_social_follow .tve_social_items ' ),
			'PreviewList' => array( 'css_prefix' => tcb_selection_root() . ' .thrv_social_follow .tve_social_items ' ),
		);
		$components['layout']['disabled_controls'] = array( 'Width', 'Height', 'Display', 'Overflow', 'Alignment' );

		unset( $components['icon'] );

		return $components;
	}

}
