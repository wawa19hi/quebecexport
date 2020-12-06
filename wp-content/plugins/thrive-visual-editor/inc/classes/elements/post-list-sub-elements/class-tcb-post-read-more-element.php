<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_Read_More_Element
 */
class TCB_Post_Read_More_Element extends TCB_Button_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Read More', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'post-read-more';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-post-read-more';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return TCB_Post_List::elements_group_label();
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	protected function html() {
		return  tcb_template( 'elements/read-more.php', $this, true );;
	}

	/**
	 * Read more components - more or less the same as the ones from the button
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['button']['disabled_controls']    = array( '.tcb-button-link-container' );
		$components['animation']['disabled_controls'] = array( '.btn-inline.anim-link' );

		$components['scroll'] = array( 'hidden' => true );

		$components = array_merge( $components, $this->shared_styles_component() );
		/* hide the Save button */
		$components['shared-styles']['disabled_controls'] = array( '.save-as-global-style' );

		return $components;
	}
}
