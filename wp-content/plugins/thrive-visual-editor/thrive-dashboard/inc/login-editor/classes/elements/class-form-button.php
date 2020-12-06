<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Login_Editor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_Button
 * @package TVD\Login_Editor
 */
class Form_Button extends \TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Form button', TVE_DASH_TRANSLATE_DOMAIN );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '#wp-submit';
	}

	public function own_components() {
		$components = $this->general_components();

		$components = array_merge( $components, array(
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
		) );

		$components['layout']['disabled_controls'] = array( 'Display', 'Alignment', '.tve-advanced-controls' );

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, array( 'css_suffix', 'css_prefix' ) ) ) {
				continue;
			}

			$components['typography']['config'][ $control ]['css_suffix'] = array( '' );
		}

		return $components;
	}

	public function hide() {
		return true;
	}

	public function has_hover_state() {
		return true;
	}
}

return new Form_Button( 'tvd-login-form-button' );
