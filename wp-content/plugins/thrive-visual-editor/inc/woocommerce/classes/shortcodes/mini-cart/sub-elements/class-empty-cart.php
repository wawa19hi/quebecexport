<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\MiniCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Empty_Cart
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Empty_Cart extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Empty Cart', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-mini-cart__empty-message';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return $this->_components();
	}
}

return new Empty_Cart( 'wc-empty-cart' );
