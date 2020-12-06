<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 6/29/2018
 * Time: 10:01 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Button_Group_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Button Group', 'thrive-cb' );
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
	 * When element is selected in editor this identifier
	 * establishes element _type
	 *
	 * @see TVE.main.element_selected() TVE._type()
	 *
	 * @return string
	 */
	public function identifier() {

		return '.thrv-button-group';
	}

	/**
	 * Components that apply only to this
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'button_group' => array(
				'config' => array(),
			),
			'typography'   => array( 'hidden' => true ),
			'animation'    => array( 'hidden' => true ),
			'shadow'       => array(
				'config' => array(
					'disabled_controls' => array( 'text' ),
				),
			),
		);
	}
}
