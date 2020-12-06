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
 * Class TCB_Pagination_Prev_Next_Button_Element
 */
class TCB_Pagination_Prev_Next_Button_Element extends TCB_Button_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Next/Previous Buttons', 'thrive-cb' );
	}

	/**
	 * Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_Pagination_Numeric::PREV_NEXT_BUTTON_CLASS;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return TCB_Utils::get_pagination_button_config( parent::own_components() );
	}

	/**
	 * Hide this element in the sidebar.
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}
}
