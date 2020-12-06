<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/20/2017
 * Time: 9:34 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Responsivevideo_Element
 */
class TCB_Responsivevideo_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Video', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'media';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'responsive_video';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_responsive_video,.tve_responsive_video_container';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'responsivevideo' => array(
				'config' => array(
					'style'           => array(
						'config' => array(
							'label'   => __( 'Video Style', 'thrive-cb' ),
							'items'   => array(
								'rv_style_none'          => __( 'No Style', 'thrive-cb' ),
								'rv_style_grey_monitor'  => __( 'Gray Monitor', 'thrive-cb' ),
								'rv_style_black_monitor' => __( 'Black Monitor', 'thrive-cb' ),
								'rv_style_black_tablet'  => __( 'Black Tablet', 'thrive-cb' ),
								'rv_style_white_tablet'  => __( 'White Tablet', 'thrive-cb' ),
								'rv_style_white_frame'   => __( 'White Frame', 'thrive-cb' ),
								'rv_style_gray_frame'    => __( 'Gray Frame', 'thrive-cb' ),
								'rv_style_dark_frame'    => __( 'Dark Frame', 'thrive-cb' ),
								'rv_style_light_frame'   => __( 'Light Frame', 'thrive-cb' ),
								'rv_style_lifted_style1' => __( 'Lifted Style 1', 'thrive-cb' ),
								'rv_style_lifted_style2' => __( 'Lifted Style 2', 'thrive-cb' ),
								'rv_style_lifted_style3' => __( 'Lifted Style 3', 'thrive-cb' ),
								'rv_style_lifted_style4' => __( 'Lifted Style 4', 'thrive-cb' ),
								'rv_style_lifted_style5' => __( 'Lifted Style 5', 'thrive-cb' ),
								'rv_style_lifted_style6' => __( 'Lifted Style 6', 'thrive-cb' ),
							),
							'default' => 'rv_style_none',
						),
					),
					'ExternalFields'  => array(
						'config'  => array(
							'key'               => 'video',
							'shortcode_element' => '.tcb-responsive-video',
						),
						'extends' => 'CustomFields',
					),
					'performance'     => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Performance Optimizations', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'adjustable_size' => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Adjustable player size', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'custom_aspect'   => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Custom aspect ratio & orientation', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'custom_time'     => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Custom start / end times', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'anonymize'       => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Anonymize viewing data', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
				),
			),
			'typography'      => array( 'hidden' => true ),
			'background'      => array( 'hidden' => true ),
			'shadow'          => array(
				'config' => array(
					'disabled_controls' => array( 'inner', 'text' ),
				),
			),
			'animation'       => array( 'hidden' => true ),
			'layout'          => array(
				'config'            => array(
					'Width' => array(
						'important' => true,
					),
				),
				'disabled_controls' => array( 'Overflow' ),
			),
		);
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
