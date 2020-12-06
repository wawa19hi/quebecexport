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
 * Class TCB_Tweet_Element
 */
class TCB_Tweet_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Click to Tweet', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'social';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'click_2_tweet';
	}

	/**
	 * Tweet element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_tw_qs';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'click-tweet' => array(
				'config' => array(
					'LabelText'        => array(
						'config'  => array(
							'label' => __( 'Label Text', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'TweetText'        => array(
						'config'  => array(
							'label' => __( 'Tweet Text', 'thrive-cb' ),
						),
						'extends' => 'Textarea',
					),
					'ShareUrlCheckbox' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Custom Share URL', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'ShareUrlInput'    => array(
						'config'  => array(
							'label'       => '',
							'placeholder' => 'http://',
						),
						'extends' => 'LabelInput',
					),
					'ViaUsername'      => array(
						'config'  => array(
							'label' => __( 'Via', 'thrive-cb' ) . '<span class="extra-input-prefix">@</span>',
						),
						'extends' => 'LabelInput',
					),
				),
			),
			'typography'  => array(
				'config' => array(
					'FontColor' => array(
						'important' => true,
					),
				),
			),
			'borders'     => array(
				'disabled_controls' => array( 'Corners', 'hr' ),
				'config'            => array(),
			),
			'background'  => array(
				'config' => array(
					'css_suffix' => ' .thrv_tw_qs_container',
				),
			),
			'shadow'      => array(
				'config' => array(
					'to' => '.thrv_tw_qs_container',
				),
			),
			'animation'   => array( 'hidden' => true ),
			'layout'      => array(
				'disabled_controls' => array(
					'Overflow',
				),
			),
		);
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
		return $this->get_thrive_advanced_label();
	}
}
