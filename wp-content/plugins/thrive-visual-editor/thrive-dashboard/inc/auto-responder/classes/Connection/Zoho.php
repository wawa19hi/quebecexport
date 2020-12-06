<?php

class Thrive_Dash_List_Connection_Zoho extends Thrive_Dash_List_Connection_Abstract {

	/**
	 * Api title
	 *
	 * @return string
	 */
	public function getTitle() {

		return 'Zoho';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function outputSetupForm() {

		$this->_directFormHtml( 'zoho' );
	}

	/**
	 * @return mixed|Thrive_Dash_List_Connection_Abstract
	 * @throws Exception
	 */
	public function readCredentials() {

		$this->setCredentials( $_POST['connection'] );

		$result = $this->testConnection();

		if ( $result !== true ) {
			return $this->error( __( 'Could not connect to Zoho using provided credentials.', TVE_DASH_TRANSLATE_DOMAIN ) );
		}

		$this->_save();
		$this->saveOauthCredentials();

		return $this->success( __( 'Zozo connected successfully', TVE_DASH_TRANSLATE_DOMAIN ) );
	}

	/**
	 * Save oauth details
	 *
	 * @throws Exception
	 */
	public function saveOauthCredentials() {

		/**
		 * @var $api Thrive_Dash_Api_Zoho
		 */
		$api = $this->getApi();

		$this->_credentials = array_merge( $this->_credentials, $api->getOauth()->getTokens() );

		$this->_save();
	}

	/**
	 * Remove access code value from credentials since it's only valid once
	 * Save the connection details
	 */
	private function _save() {

		unset( $this->_credentials['access_code'] );

		$this->save();
	}

	/**
	 * @return bool|string
	 */
	public function testConnection() {

		return is_array( $this->_getLists() );
	}

	/**
	 * Add subscriber to a list
	 *
	 * @param string $list_identifier
	 * @param array  $arguments
	 *
	 * @return bool|mixed|string
	 */
	public function addSubscriber( $list_identifier, $arguments ) {

		try {
			/**
			 * @var $api Thrive_Dash_Api_Zoho
			 */
			$api = $this->getApi();

			list( $first_name, $last_name ) = $this->_getNameParts( $arguments['name'] );

			$contact_info = array(
				'Contact Email' => $arguments['email'],
				'First Name'    => $first_name,
				'Last Name'     => $last_name,
			);

			if ( ! empty( $arguments['phone'] ) ) {
				$contact_info['Phone'] = sanitize_text_field( $arguments['phone'] );
			}

			$contact_info = array_merge( $contact_info, $this->_generateCustomFields( $arguments ) );

			$args = array(
				'listkey'     => $list_identifier,
				'contactinfo' => json_encode( $contact_info ),
			);

			$api->addSubscriber( $args );

			return true;
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * @return mixed|Thrive_Dash_Api_Zoho
	 * @throws Exception
	 */
	protected function _apiInstance() {

		return new Thrive_Dash_Api_Zoho( $this->getCredentials() );
	}

	/**
	 * Get lists
	 *
	 * @return array|bool
	 */
	protected function _getLists() {
		$lists = array();

		try {

			/**
			 * @var $api Thrive_Dash_Api_Zoho
			 */
			$api    = $this->getApi();
			$result = $api->getLists();

			if ( isset( $result['status'] ) && 'error' === $result['status'] ) {
				return false;
			}

			if ( ! empty( $result['list_of_details'] ) && is_array( $result['list_of_details'] ) ) {
				foreach ( $result['list_of_details'] as $list ) {
					$lists[] = array(
						'id'   => $list['listkey'],
						'name' => $list['listname'],
					);
				}
			}

			if ( $api->getOauth()->isAccessTokenNew() ) {
				$this->saveOauthCredentials();
			}

		} catch ( Exception $e ) {
			return false;
		}

		return $lists;
	}

	/**
	 * @param array $params
	 * @param bool  $force
	 * @param bool  $get_all
	 *
	 * @return array|mixed
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = false ) {

		$cached_data = $this->_get_cached_custom_fields();
		if ( false === $force && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		$custom_data = array();

		try {
			/** @var Thrive_Dash_Api_Zoho $api */
			$api           = $this->getApi();
			$custom_fields = $api->getCustomFields();

			if ( empty( $custom_fields['response']['fieldnames']['fieldname'] ) ) {
				$this->_save_custom_fields( $custom_data );

				return $custom_data;
			}

			foreach ( $custom_fields['response']['fieldnames']['fieldname'] as $field ) {

				if ( 'custom' !== $field['TYPE'] ) {
					continue;
				}

				$custom_data[] = $this->_normalizeCustomFields( $field );
			}
		} catch ( Exception $e ) {
		}

		$this->_save_custom_fields( $custom_data );

		return $custom_data;
	}

	/**
	 * Normalize custom field data
	 *
	 * @param $field
	 *
	 * @return array
	 */
	protected function _normalizeCustomFields( $field ) {

		$field = (array) $field;

		return array(
			'id'    => isset( $field['FIELD_ID'] ) ? $field['FIELD_ID'] : '',
			'name'  => ! empty( $field['FIELD_DISPLAY_NAME'] ) ? $field['FIELD_DISPLAY_NAME'] : '',
			'type'  => 'custom',
			'label' => ! empty( $field['FIELD_DISPLAY_NAME'] ) ? $field['FIELD_DISPLAY_NAME'] : '',
		);
	}

	/**
	 * Generate custom fields array
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private function _generateCustomFields( $args ) {
		$custom_fields = $this->get_api_custom_fields( array() );
		$ids           = $this->buildMappedCustomFields( $args );
		$result        = array();

		foreach ( $ids as $key => $id ) {
			$field = array_filter(
				$custom_fields,
				function ( $item ) use ( $id ) {
					return $item['id'] === $id['value'];
				}
			);

			$field = array_values( $field );

			if ( ! isset( $field[0] ) ) {
				continue;
			}
			$name         = strpos( $id['type'], 'mapping_' ) !== false ? $id['type'] . '_' . $key : $key;
			$cf_form_name = str_replace( '[]', '', $name );

			$result[ $field[0]['name'] ] = $this->processField( $args[ $cf_form_name ] );
		}

		return $result;
	}

	/**
	 * Build mapped custom fields array based on form params
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function buildMappedCustomFields( $args ) {
		$mapped_data = array();

		// Should be always base_64 encoded of a serialized array
		if ( empty( $args['tve_mapping'] ) || ! tve_dash_is_bas64_encoded( $args['tve_mapping'] ) || ! is_serialized( base64_decode( $args['tve_mapping'] ) ) ) {
			return $mapped_data;
		}

		$form_data = unserialize( base64_decode( $args['tve_mapping'] ) );

		$mapped_fields = $this->getMappedFieldsIDs();

		foreach ( $mapped_fields as $mapped_field_name ) {

			// Extract an array with all custom fields (siblings) names from form data
			// {ex: [mapping_url_0, .. mapping_url_n] / [mapping_text_0, .. mapping_text_n]}
			$cf_form_fields = preg_grep( "#^{$mapped_field_name}#i", array_keys( $form_data ) );

			if ( ! empty( $cf_form_fields ) && is_array( $cf_form_fields ) ) {

				foreach ( $cf_form_fields as $cf_form_name ) {
					if ( empty( $form_data[ $cf_form_name ][ $this->_key ] ) ) {
						continue;
					}

					$field_id = str_replace( $mapped_field_name . '_', '', $cf_form_name );

					$mapped_data[ $field_id ] = array(
						'type'  => $mapped_field_name,
						'value' => $form_data[ $cf_form_name ][ $this->_key ],
					);
				}
			}
		}

		return $mapped_data;
	}
}
