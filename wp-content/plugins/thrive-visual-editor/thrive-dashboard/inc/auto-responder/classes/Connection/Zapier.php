<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 23-Jul-19
 * Time: 04:27 PM
 */

class Thrive_Dash_List_Connection_Zapier extends Thrive_Dash_List_Connection_Abstract {

	/**
	 * Needed to decide webhook's name
	 *
	 * @var string
	 */
	protected $_hook_prefix = 'td_';

	/**
	 * Needed to decide webhook's name
	 *
	 * @var string
	 */
	protected $_hook_suffix = '_webhook';

	/**
	 * Accepted subscribe parameters
	 *
	 * @var array
	 */
	protected $_accepted_params
		= array(
			'first_name',
			'last_name',
			'full_name',
			'name',
			'email',
			'message',
			'phone',
			'url',
			'tags',
			'zapier_send_ip',
			'zapier_tags',
			'zapier_source_url',
			'zapier_thriveleads_group',
			'zapier_thriveleads_type',
			'zapier_thriveleads_name',
			'optin_hook',
		);

	/**
	 * @return string
	 */
	public function getTitle() {
		return 'Zapier';
	}

	/**
	 * Template
	 */
	public function outputSetupForm() {
		$this->_directFormHtml( 'zapier' );
	}

	/**
	 * Read credentials from $_POST and try to save them
	 *
	 * @return string|true
	 */
	public function readCredentials() {

		$this->setCredentials(
			array(
				'api_key'  => sanitize_text_field( $_POST['connection']['api_key'] ),
				'blog_url' => sanitize_text_field( $_POST['connection']['blog_url'] ),
			)
		);

		$_test_passed = $this->testConnection();

		if ( true === $_test_passed ) {
			$this->save();
		}

		return $_test_passed;
	}


	/**
	 * Delete Zapier saved options
	 *
	 * @return $this|Thrive_Dash_List_Connection_Abstract
	 */
	public function beforeDisconnect() {

		foreach ( array( 'td_api_key', 'td_optin_webhook', 'td_cf-optin_webhook' ) as $option_name ) {
			delete_option( $option_name );
		}

		return $this;
	}

	/**
	 * @return true|string true on SUCCESS or error message on FAILURE
	 */
	public function testConnection() {

		$_is_working = true;

		/** @var Thrive_Dash_Api_Zapier $api */
		$api = $this->getApi();

		/** @var WP_Error|bool $response */
		$response = $api->authenticate();

		if ( is_wp_error( $response ) ) {
			$_is_working = $response->get_error_message();
		}

		return $_is_working;
	}

	/**
	 * Calls a Zapier trigger in order to start the created Zapier flow with different integrations
	 * based on the received hook URL [for Lead Generation / or Contact Form]
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed|Thrive_Dash_List_Connection_Abstract
	 */
	public function addSubscriber( $list_identifier, $arguments ) {

		$params        = $this->_prepare_args( $arguments );
		$subscribe_url = $this->_get_hook_url( $arguments );

		if ( ! empty( $subscribe_url ) ) {

			return $this->getApi()->trigger_subscribe( $subscribe_url, $params );
		}

		return $this->error( __( 'There was an error sending your message, please make sure your Zap is activated or contact support.', TVE_DASH_TRANSLATE_DOMAIN ) );
	}

	/**
	 * Get the proper hook URL from options
	 *
	 * @param $arguments
	 *
	 * @return string
	 */
	private function _get_hook_url( $arguments ) {

		// for Lead Generation
		$hook_name = 'optin';

		// for Contact Form
		if ( ! empty( $arguments['optin_hook'] ) && in_array( 'optin_hook', $this->_accepted_params, true ) ) {
			$hook_name = filter_var( $arguments['optin_hook'], FILTER_SANITIZE_STRING );
		}

		// Get subscribed hook option
		return (string) get_option( $this->_get_option_name( $hook_name ), '' );
	}

	/**
	 * Build and sanitize param array
	 *
	 * @param $arguments
	 *
	 * @return array
	 */
	private function _prepare_args( $arguments ) {

		$params = array();

		if ( empty( $arguments ) || ! is_array( $arguments ) ) {
			return $params;
		}

		foreach ( $arguments as $param => $value ) {

			$param = (string) $param;
			if ( in_array( $param, $this->_accepted_params, true ) ) {

				switch ( strtolower( $param ) ) {
					case 'zapier_send_ip':
						if ( 1 === (int) $value ) {
							$params['ip_address'] = tve_dash_get_ip();
						}
						break;
					case 'zapier_tags':
						$params['tags'] = ! empty( $value ) ? filter_var_array( explode( ',', $value ), FILTER_SANITIZE_STRING ) : array();
						break;
					case 'zapier_thriveleads_group':
						// Get title by Group ID
						$params['thriveleads_group'] = (int) $value > 0 ? get_the_title( (int) $value ) : '';
						break;
					case 'zapier_thriveleads_type':
						$params['thriveleads_type'] = filter_var( $value, FILTER_SANITIZE_STRING );
						break;
					case 'zapier_thriveleads_name':
						$params['thriveleads_name'] = filter_var( $value, FILTER_SANITIZE_STRING );
						break;
					case 'url':
						$params['website'] = filter_var( $value, FILTER_SANITIZE_URL );
						break;
					default:
						if ( ! empty( $value ) ) {
							$params[ $param ] = filter_var( $value, FILTER_SANITIZE_STRING );
						}
						break;
				}
			}
		}

		$params['source_url'] = filter_var( $_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL );

		// Add all dynamic messages for textarea
		$messages = array();
		foreach ( $arguments as $key => $val ) {
			if ( strpos( $key, 'mapping_textarea_' ) === 0 ) {
				$messages[] = $arguments[ $key ];
			}
		}

		if ( ! empty( $messages ) ) {
			$params['message'] = $messages;
		}

		return $params;
	}

	/**
	 * @return mixed|Thrive_Dash_Api_Zapier
	 */
	protected function _apiInstance() {

		return new Thrive_Dash_Api_Zapier( $this->param( 'api_key' ), $this->param( 'blog_url' ) );
	}

	/**
	 * @return array|bool
	 */
	protected function _getLists() {
		return array();
	}

	/**
	 * @return string
	 */
	public static function getType() {
		return 'integrations';
	}

	/**
	 * Used to populate the api value on connecting card
	 *
	 * @return string
	 */
	public function get_api_key() {

		$api_key = get_option( 'td_api_key', null );

		if ( empty( $api_key ) ) {
			$api_key = tve_dash_generate_api_key();
			update_option( 'td_api_key', $api_key );
		}

		return $api_key;
	}

	/**
	 * Used to populate the input value on connecting card
	 *
	 * @return string
	 */
	public function get_blog_url() {

		return site_url();
	}

	/**
	 * @param $hook_name
	 *
	 * @return string
	 */
	protected function _get_option_name( $hook_name ) {

		return $this->_hook_prefix . $hook_name . $this->_hook_suffix;
	}
}

