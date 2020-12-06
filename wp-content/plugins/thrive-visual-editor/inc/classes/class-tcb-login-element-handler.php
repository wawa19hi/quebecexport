<?php

/**
 * Class TCB_Login_Element_Handler
 *
 * Handle Login element submit
 */
class TCB_Login_Element_Handler {

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			add_action( 'wp_ajax_nopriv_tve_login_submit', array( $this, 'submit' ) );
			add_action( 'wp_ajax_tve_login_submit', array( $this, 'submit' ) );
		}

		add_action( 'tcb_login_action_login', array( $this, 'action_login' ) );
		add_action( 'tcb_login_action_register', array( $this, 'action_register' ) );
		add_action( 'tcb_login_action_recover_password', array( $this, 'action_recover_password' ) );
		add_filter( 'tcb_dynamiclink_data', array( $this, 'dynamiclink_data' ), 100 );

		add_shortcode( 'thrive_login_form_shortcode', array( $this, 'login_form_shortcode' ) );
	}

	/**
	 * Remove actions and filters that might affect submit action
	 */
	private function _clear() {

		global $WishListMemberInstance;

		$is_wl = class_exists( 'WishListMember3', false ) && $WishListMemberInstance instanceof WishListMember3;

		if ( true === $is_wl ) {
			remove_action( 'template_redirect', array( $WishListMemberInstance, 'Process' ), 1 );
		}
	}

	/**
	 * Handle Submit action
	 */
	public function submit() {

		$this->_clear();

		$data = $_POST;

		if ( isset( $data['custom_action'] ) ) {

			/**
			 * Fire a hook for each action of the Login Element
			 */
			do_action( 'tcb_login_action_' . $data['custom_action'], $data );
		}

		wp_send_json( array( 'error' => 'ERROR!! No handler provided for the request' ) );
	}

	/**
	 * Handle Login Action for Login Element
	 *
	 * @param array $data
	 * @param bool  $json_output Whether or not to send the output as a json-encoded response to the browser
	 *
	 * @return mixed|void
	 */
	public function action_login( $data, $json_output = true ) {
		$args['user_login']    = isset( $data['username'] ) ? sanitize_text_field( $data['username'] ) : '';
		$args['user_password'] = isset( $data['password'] ) ? sanitize_text_field( $data['password'] ) : '';
		$args['remember']      = ! empty( $data['remember_me'] );

		/**
		 * Do not send back the password
		 */
		unset( $data['password'] );

		$user = wp_signon( $args );

		$data['success'] = $user instanceof WP_User;
		$data['errors']  = $user instanceof WP_Error ? $user->get_error_messages() : array();

		/**
		 * Allow other plugins to manipulate the response
		 *
		 * @param array $data array of data to be sent back
		 *
		 * @return array
		 */
		$data = apply_filters( 'tcb_after_user_logged_in', $data );

		if ( ! $json_output ) {
			return $data;
		}

		wp_send_json( $data );
	}

	/**
	 * Handle Forgot Password Action for Login Element
	 *
	 * @param array $data
	 */
	public function action_recover_password( $data ) {
		$data['success'] = false;
		$data['errors']  = array();

		$user_data = $this->get_user( sanitize_text_field( $data['login'] ) );

		if ( ! empty( $user_data['errors'] ) ) {

			$data['errors'] = $user_data['errors'];

			wp_send_json( $data );
		}

		$key = get_password_reset_key( $user_data['user'] );

		if ( is_wp_error( $key ) ) {
			$data['errors']['nokey'] = __( 'Failed to reset password. Please try again', 'thrive-cb' );

			wp_send_json( $data );
		}

		$result = $this->send_recover_pass_msg( $user_data['user'], $key );

		$data['success'] = $result;

		if ( true !== $result ) {
			$data['errors']['failed_email'] = __( 'Failed to send password recovery email!', 'thrive-cb' );
		}

		wp_send_json( $data );
	}

	/**
	 * Send Recover Password Message
	 *
	 * @param WP_User $user
	 * @param string  $key
	 *
	 * @return bool
	 */
	public function send_recover_pass_msg( WP_User $user, $key ) {
		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		ob_start();

		include tve_editor_path( 'inc/views/actions/login/recover-password.php' );

		$message = ob_get_contents();

		ob_end_clean();

		/**
		 * Password Recover Email Subject
		 */
		$title = sprintf( __( '[%s] Password Reset' ), $site_name );

		return wp_mail( $user->user_email, wp_specialchars_decode( $title ), $message, 'Content-Type: text/html; charset=UTF-8' );
	}

	/**
	 * Registration form submission process
	 *
	 * @param array $data
	 */
	public function action_register( $data ) {
		/**
		 * Handle the WordPress API Connection separately, because if this one fails, there is no point in processing further API connections
		 */
		add_filter( 'tcb_api_subscribe_connections', function ( $connections, $available, $post_data ) {
			if ( ! isset( $connections['wordpress'], $available['wordpress'] ) ) {
				$this->send_ajax_error( array(
					'error' => __( 'Something went wrong. Please contact site owner', 'thrive-cb' ),
				) );
			}
			/** @var Thrive_Dash_List_Connection_Wordpress $wordpress_api */
			$wordpress_api = $available['wordpress'];
			$wordpress_api->set_error_type( 'array' );

			/* get profile fields from mapping */
			if ( ! empty( $post_data['tve_mapping'] ) ) {
				foreach ( Thrive_Dash_List_Manager::decodeConnectionString( $post_data['tve_mapping'] ) as $field_name => $spec ) {
					if ( ! empty( $spec['_field'] ) ) {
						$post_data[ $spec['_field'] ] = $post_data[ $field_name ];
					}
				}
			}

			$submission_result = tve_api_add_subscriber( $available['wordpress'], $connections['wordpress'], $post_data );
			if ( $submission_result !== true ) {
				$this->send_ajax_error( $submission_result );
			}
			unset( $connections['wordpress'] );

			return $connections;
		}, 10, 3 );

		$logged_in = false;
		/**
		 * Use this detection method, because `is_user_logged_in()` function will not work in the same request as the login process
		 */
		add_action( 'wp_login', static function () use ( &$logged_in ) {
			$logged_in = true;
		} );

		$result = tve_api_form_submit( false );

		if ( ! is_array( $result ) ) {
			wp_send_json( $result );
		}

		if ( ! empty( $result['error'] ) ) {
			$this->send_ajax_error( $result );
		}

		/* This filter is incorrectly named. It should be renamed to something like "tcb_prepare_login_response". Kept to maintain backwards compat. */
		$result = apply_filters( 'tcb_after_user_logged_in', $result );

		$result['success']   = true;
		$result['logged_in'] = $logged_in;

		/* WordPress API already successfully registered the user */
		wp_send_json( array( 'wordpress' => true ) + $result );
	}

	/**
	 * Send a response containing an ajax error message
	 *
	 * @param mixed $data
	 * @param int   $http_status_code
	 */
	public function send_ajax_error( $data, $http_status_code = 422 ) {
		status_header( $http_status_code );
		wp_send_json( $data );
	}

	/**
	 * @param string $data
	 *
	 * @return array
	 */
	public function get_user( $data ) {

		$response = array();

		$response['user']   = '';
		$response['errors'] = array();

		if ( empty( $data ) || ! is_string( $data ) ) {
			$response['errors']['empty_username'] = __( 'Enter a username or email address.', 'thrive-cb' );

			return $response;
		}

		$data      = sanitize_text_field( $data );
		$field     = strpos( $data, '@' ) ? 'email' : 'login';
		$user_data = get_user_by( $field, $data );

		if ( ! $user_data instanceof WP_User ) {
			$response['errors']['invalidcombo'] = __( 'Invalid username or email.', 'thrive-cb' );
		}

		$response['user'] = $user_data;

		return $response;
	}

	/**
	 * Get available actions for Login element
	 *
	 * @return array
	 */
	public static function get_post_login_actions() {

		$actions = array(
			array(
				'key'          => 'refresh',
				'label'        => __( 'Refresh page', 'thrive_cb' ),
				'icon'         => 'autorenew',
				'preview_icon' => 'autorenew',
			),
			array(
				'key'          => 'redirect',
				'label'        => __( 'Redirect to Custom URL', 'thrive_cb' ),
				'icon'         => 'url',
				'preview_icon' => 'url',
			),
			array(
				'key'          => 'noRedirect', // noRedirect key kept for backwards compatibility
				'label'        => __( 'Switch to already logged in state', 'thrive_cb' ),
				'icon'         => 'change',
				'preview_icon' => 'change',
			),
			array(
				'key'          => 'showMessage',
				'label'        => __( 'Show success notification', 'thrive_cb' ),
				'icon'         => 'notif',
				'preview_icon' => 'notif',
			),
		);

		/**
		 * Allows dynamically modifying post login actions.
		 *
		 * @param array $actions array of actions to be filtered
		 *
		 * @return array
		 */
		return apply_filters( 'tcb_post_login_actions', $actions );
	}

	/**
	 * Get available post-registration actions for Login element
	 *
	 * @return array
	 */
	public static function get_post_register_actions() {

		$actions = array_filter(
			static::get_post_login_actions(),
			static function ( $action ) {
				/* identical to post login actions, except for "Show already logged in state" */

				return $action['key'] !== 'noRedirect';
			}
		);

		/**
		 * Allows dynamically modifying post-registration actions.
		 *
		 * @param array $actions array of actions to be filtered
		 *
		 * @return array
		 */
		return apply_filters( 'tcb_post_register_actions', array_values( $actions ) );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function dynamiclink_data( $data ) {

		$data['Login Form'] = array(
			'links'     => array(
				0 => array(
					'bk_to_login' => array(
						'name' => 'Back to Login',
						'url'  => '',
						'show' => 1,
						'id'   => 'bk_to_login',
						'type' => 'login',
					),
					'pass_reset'  => array(
						'name' => 'Password Reset',
						'url'  => '',
						'show' => 1,
						'id'   => 'forgot_password',
						'type' => 'login',
					),
					'logout'      => array(
						'name' => 'Logout',
						'url'  => wp_logout_url(),
						'show' => 1,
						'id'   => 'logout',
						'type' => [ 'login', 'register' ],
					),
					'login'       => array(
						'name' => 'Log In',
						'url'  => '',
						'show' => 1,
						'id'   => 'login',
						'type' => 'login',
					),
					'register'    => array(
						'name' => 'Register',
						'url'  => '',
						'show' => 1,
						'id'   => 'register',
						'type' => 'register',
					),
				),
			),
			'shortcode' => 'thrive_login_form_shortcode',
		);

		return $data;
	}

	/**
	 * @param $args
	 *
	 * @return string
	 */
	public function login_form_shortcode( $args ) {

		if ( ! isset( $args['id'] ) ) {

			return '';
		}

		$data = '#tcb-state--' . $args['id'];

		switch ( $args['id'] ) {
			case 'logout':
				global $wp;
				$data = wp_logout_url( home_url( add_query_arg( array(), $wp->request ) ) );
				break;
			default;
				break;
		}

		return $data;
	}

	/**
	 * Get a list of all available error messages for the registration form element
	 *
	 * @return array
	 */
	public static function get_registration_error_messages() {
		return array(
			'required_field'   => __( 'This field is required', 'thrive-cb' ),
			'file_size'        => __( '{file} exceeds the maximum file size of {filelimit}', 'thrive-cb' ),
			'file_extension'   => __( 'Sorry, {fileextension} files are not allowed', 'thrive-cb' ),
			'max_files'        => __( 'Sorry, the maximum number of files is {maxfiles}', 'thrive-cb' ),
			'passwordmismatch' => __( 'Password mismatch', 'thrive-cb' ),
		);
	}

	public static function get_default_settings() {
		return array(
			'submit_action'                        => 'showMessage',
			'redirect_url'                         => '',
			'success_message'                      => 'Success',
			'post_register_action'                 => 'showMessage',
			'post_register_action.success_message' => 'Success',
			'post_register_action.redirect_url'    => '',
		);
	}

	public static function get_registration_form_default_settings() {
		return array(
			'v'       => 1,
			'apis'    => array(
				'wordpress' => 'subscriber',
			),
			'captcha' => 0,
		);
	}

	/**
	 * Configuration for Group editing inside Login / Registration forms
	 *
	 * @return array
	 */
	public static function get_group_editing_options() {
		return array(
			'exit_label'    => __( 'Exit Group Styling', 'thrive-cb' ),
			'select_values' => array(
				array(
					'value'    => 'all_form_items',
					'selector' => '.tve-login-form-item',
					'name'     => __( 'Grouped Form Items', 'thrive-cb' ),
					'singular' => __( '-- Form Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_inputs',
					'selector' => '.tve-login-form-input',
					'name'     => __( 'Grouped Inputs', 'thrive-cb' ),
					'singular' => __( '-- Input %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_labels',
					'selector' => '.tve-login-form-item .tcb-label',
					'name'     => __( 'Grouped Labels', 'thrive-cb' ),
					'singular' => __( '-- Label %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_radio_elements',
					'selector' => '.tve_lg_radio',
					'name'     => __( 'Grouped Radio', 'thrive-cb' ),
					'singular' => __( '-- Radio %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_checkbox_elements',
					'selector' => '.tve_lg_checkbox:not(.tcb-lg-consent)',
					'name'     => __( 'Grouped Checkbox', 'thrive-cb' ),
					'singular' => __( '-- Checkbox %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_dropdown_elements',
					'selector' => '.tve_lg_dropdown',
					'name'     => __( 'Grouped Dropdowns', 'thrive-cb' ),
					'singular' => __( '-- Dropdown %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'radio_options',
					'selector' => '.tve_lg_radio_wrapper',
					'name'     => __( 'Grouped Radio Options', 'thrive-cb' ),
					'singular' => __( '-- Option %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'dropdown_options',
					'selector' => '.tve-lg-dropdown-option',
					'name'     => __( 'Grouped Dropdown Options', 'thrive-cb' ),
					'singular' => __( '-- Option %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'checkbox_options',
					'selector' => '.tve_lg_checkbox_wrapper:not(.tcb-lg-consent .tve_lg_checkbox_wrapper)',
					'name'     => __( 'Grouped Checkbox Options', 'thrive-cb' ),
					'singular' => __( '-- Option %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_form_link',
					'selector' => '.tar-login-elem-link',
					'name'     => __( 'Form Links', 'thrive-cb' ),
					'singular' => __( '-- Link %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_form_link_text',
					'selector' => '.tar-login-elem-link .tve-dynamic-link',
					'name'     => __( 'Form Links Texts', 'thrive-cb' ),
					'singular' => __( '-- Text %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_form_titles',
					'selector' => '.thrv-form-title',
					'name'     => __( 'Form Title', 'thrive-cb' ),
					'singular' => __( '-- Title %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_form_info',
					'selector' => '.thrv-form-info',
					'name'     => __( 'Form Texts', 'thrive-cb' ),
					'singular' => __( '-- Text %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_submit_buttons',
					'selector' => '.tar-login-elem-button',
					'name'     => __( 'Submit Buttons', 'thrive-cb' ),
					'singular' => __( '-- Label %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_submit_texts',
					'selector' => '.tar-login-submit  .tcb-button-text',
					'name'     => __( 'Submit Button Text', 'thrive-cb' ),
					'singular' => __( '-- Submit Text %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_inputs_icons',
					'selector' => '.tve-login-form-input .thrv_icon',
					'name'     => __( 'Input Icons', 'thrive-cb' ),
					'singular' => __( '-- Input Icon %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_states',
					'selector' => '.tve-form-state',
					'name'     => __( 'Form States', 'thrive-cb' ),
					'singular' => __( '-- Form State %s', 'thrive-cb' ),
				),

			),
		);
	}
}

new TCB_Login_Element_Handler();
