<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 19-Jul-19
 * Time: 09:26 AM
 */

/**
 * Class TD_REST_Hook_Controller
 *
 * Used to implement Zapier Integration but it can be extended
 * For further REST Hooks
 */
class TD_REST_Hook_Controller extends TD_REST_Controller {

	/**
	 * The base of this controller's route.
	 *
	 * @since 4.7.0
	 * @var string
	 */
	protected $rest_base;

	/**
	 * REST Hook Name
	 * - saves the webhook based on this name
	 *
	 * @var string
	 */
	protected $_hook_name;

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
	 * TD_REST_Hook_Controller constructor.
	 *
	 * @param string $hook_name
	 */
	public function __construct( $hook_name = '' ) {

		parent::__construct();

		$this->_hook_name = (string) $hook_name;
		$this->rest_base  = trailingslashit( $hook_name ) . 'subscription';
	}

	/**
	 * Register routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'subscribe' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/sample',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'sample' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'unsubscribe' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
	}

	/**
	 * The endpoint where the Integration subscribes the webhook
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function subscribe( $request ) {

		$error = new WP_Error( 'td_invalid_hook_url', __( 'Invalid Hook URL', TVE_DASH_TRANSLATE_DOMAIN ) );

		// Param from Lead Generation auth
		$hook_url = $request->get_param( 'hook_url' );

		// Param from Contact Form auth
		if ( ! $hook_url ) {
			$hook_url = $request->get_param( 'hookUrl' );
		}

		$hook_url = strpos( $hook_url, 'http://' ) !== - 1 ? $hook_url : $error;

		if ( true === is_wp_error( $hook_url ) ) {
			return $hook_url;
		}

		update_option( $this->_get_option_name(), $hook_url );

		return array(
			'id' => $this->_get_option_name(),
		);
	}

	/**
	 * The endpoint where the Integration unsubscribes the webhook
	 *
	 * @return array|WP_Error
	 */
	public function unsubscribe() {

		$deleted = delete_option( $this->_get_option_name() );
		$return  = new WP_Error( 'td_fail_unsubscribe', __( 'Could not unsubscribe the webhook', TVE_DASH_TRANSLATE_DOMAIN ) );

		if ( true === $deleted ) {
			$return = true;
		}

		return $return;
	}

	/**
	 * Required endpoint for creating the trigger in Zapier
	 * provide a sample of fields and data
	 *
	 * @return mixed|WP_REST_Response
	 */
	public function sample() {

		// For LG Subscription
		$response_sample = array(
			array(
				'name'              => 'Full name',
				'email'             => 'name@email.com',
				'phone'             => '1231231231',
				'ip_address'        => '192.168.1.1',
				'tags'              => array(
					'tag1',
					'tag2',
					'tag3',
				),
				'message'           => array(
					'message1',
					'message2',
					'message3',
				),
				'website'           => 'https://yourwebsite.com/',
				'source_url'        => 'https://thrivethemes.com',
				'thriveleads_group' => 'Group 1',
				'thriveleads_type'  => 'Lightbox',
				'thriveleads_name'  => 'First Lightbox',
			),
		);

		// For CF Subscription [DEPRECATED should be removed when considering that old CF forms are no longer connected on the users side]
		if ( 'cf-optin' === $this->_hook_name ) {
			$response_sample = array(
				array(
					'first_name' => 'First name',
					'last_name'  => 'Last name',
					'full_name'  => 'Full name',
					'email'      => 'name@email.com',
					'message'    => 'Sample message',
					'phone'      => '1231231231',
					'website'    => 'https://yourwebsite.com/',
					'source_url' => 'https://thrivethemes.com',
					'ip_address' => '192.168.1.1',
					'tags'       => array( 'tag1', 'tag2', 'tag3' ),
				),
			);
		}

		return rest_ensure_response( $response_sample );
	}

	/**
	 * Uses hook's prefix, name, suffix to establish the option name
	 * to save the webhook
	 *
	 * @return string
	 */
	protected function _get_option_name() {

		return $this->_hook_prefix . $this->_hook_name . $this->_hook_suffix;
	}
}
