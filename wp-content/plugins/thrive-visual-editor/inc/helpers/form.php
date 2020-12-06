<?php

namespace TCB\inc\helpers;

/**
 * Class FormSettings
 *
 * @property-read array $apis        configuration for the api connections
 * @property-read int   $captcha     whether or not captcha is enabled (1 / 0)
 * @property-read array $extra       extra configuration for each api connection
 * @property-read array $custom_tags array of custom tags configuration (from radio, checkbox, dropdown)
 *
 * @package TCB\inc\helpers
 */
class FormSettings {
	public $ID;

	public $post_title;

	const POST_TYPE = '_tcb_form_settings';

	const SEP = '__TCB_FORM__';

	protected $config = array();

	/**
	 * Default configuration for forms
	 *
	 * @var array
	 */
	public static $defaults = array(
		'apis'        => array(),
		'captcha'     => 0,
		'extra'       => array(),
		'custom_tags' => array(),
	);

	public function __construct( $config ) {
		$this->set_config( $config );
	}

	/**
	 * Setter for config
	 *
	 * @param array|string $config
	 *
	 * @return $this
	 */
	public function set_config( $config ) {
		if ( is_string( $config ) ) {
			$config = json_decode( $config, true );
		}
		$this->config = wp_parse_args( $config, static::$defaults );

		return $this;
	}

	/**
	 * Get the configuration
	 *
	 * @param bool $json get it as JSON
	 *
	 * @return array|false|string
	 */
	public function get_config( $json = true ) {
		return $json ? json_encode( $this->config ) : $this->config;
	}

	/**
	 * Magic config getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$default = isset( static::$defaults[ $name ] ) ? static::$defaults[ $name ] : null;

		return isset( $this->config[ $name ] ) ? $this->config[ $name ] : $default;
	}

	/**
	 * Loads a file config from an ID, or directly with a configuration array
	 *
	 * @param string|int|null|array $id          if array, it will act as a config. If empty, return a new instance with default settings
	 * @param int                   $post_parent if sent, it will also make sure the instance has the same post parent as this
	 *
	 * @return static
	 */
	public static function get_one( $id = null, $post_parent = null ) {
		if ( is_array( $id ) ) {
			$config = $id;
			$id     = null;
		} elseif ( $id && is_numeric( $id ) ) {
			$id                  = (int) $id;
			$post                = get_post( $id );
			$post_parent_matches = true;
			if ( $post && $post_parent ) {
				$post_parent_matches = $post_parent && ( (int) $post_parent === (int) $post->post_parent );
			}
			if ( $post && $post->post_type === static::POST_TYPE && $post_parent_matches ) {
				$config = json_decode( $post->post_content, true );
			}
		}

		if ( empty( $config ) ) {
			$id     = null;
			$config = array();
		}

		$instance     = new static( $config );
		$instance->ID = $id;

		return $instance;
	}

	/**
	 * Save a File config to db
	 *
	 * @param string $post_title name to give to the post that's being saved
	 * @param array  $post_data  extra post data to save
	 *
	 * @return static|\WP_Error
	 */
	public function save( $post_title, $post_data = null ) {
		/* preserve new lines for email fields */
		array_walk_recursive( $this->config, function ( &$val, $key ) {
			if ( is_string( $val ) && strpos( $key, 'email' ) !== false ) {
				$val = nl2br( $val );
				$val = preg_replace( "/[\n]+/", "", $val );
			}
		} );

		$content = wp_json_encode( $this->config );
		$content = wp_slash( $content );

		$save_data = array(
			'post_type'    => static::POST_TYPE,
			'post_title'   => $post_title,
			'post_content' => $content,
		);
		if ( is_array( $post_data ) ) {
			$save_data += $post_data;
		}
		remove_all_filters( 'wp_insert_post_data' );
		remove_all_actions( 'edit_post' );
		remove_all_actions( 'save_post' );
		remove_all_actions( 'wp_insert_post' );
		if ( $this->ID ) {
			$save_data['ID'] = $this->ID;
			$post_id         = wp_update_post( $save_data );
		} else {
			$post_id = wp_insert_post( $save_data );
		}
		$this->ID = $post_id;

		return is_wp_error( $post_id ) ? $post_id : $this;
	}

	/**
	 * Delete the current instance
	 */
	public function delete() {
		if ( $this->ID ) {
			wp_delete_post( $this->ID );
		}

		return $this;
	}

	/**
	 * Build the regex pattern for matching form json configuration
	 *
	 * @param bool $with_attribute whether or not to also match the `data-form-settings` attribute
	 *
	 * @return string
	 */
	public static function pattern( $with_attribute = false ) {
		$regex = static::SEP . '(.+?)' . static::SEP;

		if ( $with_attribute ) {
			$regex = ' data-form-settings="' . $regex . '"';
		}

		return "#{$regex}#s";
	}

	/**
	 * Populate the $data that's sent to autoresponder based on stored settings for the form
	 *
	 * @param array $data
	 */
	public function populate_request( &$data ) {
		/* mark the current request data as trusted */
		$data['$$trusted'] = true;

		/* captcha */
		$data['_use_captcha'] = (int) $this->captcha;

		/* build custom tags list based on user-submitted values and form settings - these are set for radio, checkbox and dropdown form elements */
		$taglist = [];
		foreach ( $this->custom_tags as $field_name => $all_tags ) {
			if ( ! isset( $data[ $field_name ] ) ) {
				/* no POST data has been sent in $field_name - no tag associated*/
				continue;
			}
			$value_as_array = is_array( $data[ $field_name ] ) ? $data[ $field_name ] : array( $data[ $field_name ] );
			foreach ( $value_as_array as $submitted_value ) {
				if ( isset( $all_tags[ $submitted_value ] ) ) {
					$taglist[] = str_replace( array( '"', "'" ), '', trim( $all_tags[ $submitted_value ] ) );
				}
			}
		}
		$taglist         = implode( ',', array_filter( $taglist ) );
		$has_custom_tags = ! empty( $taglist );

		/* extra data for each api */
		foreach ( $this->extra as $api_key => $data_array ) {
			foreach ( $data_array as $field => $value ) {
				parse_str( $field, $parsed_field );
				/* parse array fields that are stored flat "field[custom_field]":"value"  */
				if ( is_array( $parsed_field ) && ! empty( $parsed_field ) ) {
					$key = array_keys( $parsed_field )[0];

					if ( is_array( $parsed_field[ $key ] ) && !empty( $parsed_field[ $key ] ) ) {
						$second_key = array_keys( $parsed_field[ $key ] )[0];

						$data[ $api_key . '_' . $key ][ $second_key ] = $value;
					} else {
						$data[ $api_key . '_' . $key ] = $value;
					}
				} else {
					$data[ $api_key . '_' . $field ] = $value;
				}
			}
			$tags_key = $api_key . '_tags';
			if ( isset( $data[ $tags_key ] ) ) {
				/* append any tags from radio/checkboxes/dropdowns */
				if ( $has_custom_tags ) {
					$data[ $tags_key ] = trim( $data[ $tags_key ] . ',' . $taglist, ',' );
				}

				/**
				 * Filter the final list of tags that gets sent to the API
				 *
				 * @param string $tags    list of tags, separated by comma
				 * @param string $api_key API connection identifier
				 *
				 * @return array
				 */
				$data[ $tags_key ] = apply_filters( 'tcb_form_api_tags', $data[ $tags_key ], $api_key );
			}
		}
	}
}

/**
 * Processes each form settings instance, saving it to the database
 *
 * @param array $forms array of form settings
 * @param int   $post_parent
 *
 * @return array map of replacements
 */
function save_form_settings( $forms, $post_parent ) {
	$replaced   = array();
	$post_title = 'Form settings' . ( $post_parent ? ' for content ' . $post_parent : '' );

	foreach ( $forms as $form ) {
		$id       = ! empty( $form['id'] ) ? (int) $form['id'] : 0;
		$instance = FormSettings::get_one( $form['id'], $post_parent )
		                        ->set_config( wp_unslash( $form['settings'] ) )
		                        ->save( $post_title, empty( $post_parent ) ? null : array( 'post_parent' => $post_parent ) );
		if ( $instance->ID !== $id ) {
			$replaced[ $form['id'] ] = $instance->ID;
		}
	}

	return $replaced;
}

/**
 * Delete one or multiple form settings
 *
 * @param array|int|string $id
 */
function delete_form_settings( $id ) {
	if ( empty( $id ) ) {
		return;
	}

	if ( ! is_array( $id ) ) {
		$id = array( $id );
	}

	foreach ( $id as $form_id ) {
		$form_id = (int) $form_id;
		FormSettings::get_one( $form_id )->delete();
	}
}

/**
 * On frontend contexts, always remove form settings from content
 */
add_filter( 'tve_thrive_shortcodes', static function ( $content, $is_editor_page ) {
	if ( $is_editor_page || strpos( $content, FormSettings::SEP ) === false ) {
		return $content;
	}

	return preg_replace( FormSettings::pattern( true ), '', $content );
}, 10, 2 );

/**
 * Process content pre-save
 */
add_filter( 'tcb.content_pre_save', static function ( $response, $post_data ) {
	/**
	 * Allows skipping the process of saving form settings to database
	 *
	 * @param bool $skip whether or not to skip
	 *
	 * @return bool
	 */
	$process_form_settings = apply_filters( 'tcb_process_form_settings', true );

	if ( $process_form_settings && ! empty( $post_data['forms'] ) ) {
		/**
		 * save form settings to the database
		 */
		$post_id = isset( $post_data['post_id'] ) ? (int) $post_data['post_id'] : 0;
		if ( ! empty( $post_data['ignore_post_parent'] ) ) {
			$post_id = null;
		}
		$response['forms'] = \TCB\inc\helpers\save_form_settings( $post_data['forms'], $post_id );
	}

	if ( $process_form_settings && ! empty( $post_data['deleted_forms'] ) ) {
		\TCB\inc\helpers\delete_form_settings( $post_data['deleted_forms'] );
	}

	return $response;
}, 10, 2 );
