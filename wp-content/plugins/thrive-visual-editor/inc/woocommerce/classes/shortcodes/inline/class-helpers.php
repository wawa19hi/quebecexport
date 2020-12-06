<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Inline;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Helpers
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Inline
 */
class Helpers {

	/**
	 * Fetch the sale/regular price for this product, format it and prepare it to be rendered
	 *
	 * @param string      $price_type - sale / regular
	 * @param \WC_Product $product
	 * @param array       $attr
	 *
	 * @return string
	 */
	public static function render_price( $price_type, $product, $attr ) {
		$price = (float) call_user_func( array( $product, "get_{$price_type}_price" ) );

		$price = static::get_formatted_price( $price, ! empty( $attr[ Main::PRICE_SHOW_DECIMALS ] ) );

		if ( ! empty( $attr[ Main::PRICE_INCLUDE_CURRENCY_SYMBOL ] ) ) {
			$price = static::add_currency_symbol( $price );
		}

		return $price;
	}

	/**
	 * Format the current price, respecting WooCommerce settings: add thousand separators, keep or remove decimals, etc
	 * The idea is to display the shortcodes just like WooCommerce displays the regular prices
	 *
	 * @param float $price
	 * @param bool  $show_decimals
	 *
	 * @return string
	 */
	public static function get_formatted_price( $price, $show_decimals = true ) {
		$decimals = $show_decimals ? wc_get_price_decimals() : 0;

		return number_format( $price, $decimals, wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
	}

	/**
	 * Add the currency symbol to the price while respecting the position from the WooCommerce settings.
	 *
	 * @param $price
	 *
	 * @return string
	 */
	private static function add_currency_symbol( $price ) {
		$prefix = '';
		$suffix = '';
		$symbol = static::get_currency_symbol();

		switch ( get_option( 'woocommerce_currency_pos' ) ) {
			case 'left_space':
				$prefix = $symbol . ' ';
				break;
			case 'left':
				$prefix = $symbol;
				break;
			case 'right_space':
				$suffix = ' ' . $symbol;
				break;
			case 'right':
				$suffix = $symbol;
				break;
			default:
				break;
		}

		return $prefix . $price . $suffix;
	}

	/**
	 * Wrapper for the WooCommerce currency symbol getter.
	 *
	 * @return string
	 */
	public static function get_currency_symbol() {
		return get_woocommerce_currency_symbol();
	}

	/**
	 * Currently available inline shortcodes
	 *
	 * @return array
	 */
	public static function available_shortcodes() {
		return array(
			'product_description' => array(
				'name'     => __( 'Product short description', 'thrive-cb' ),
				'controls' => array(),
				'type'     => '',
			),
			'_sale_price'         => array(
				'name'     => __( 'Product sale price', 'thrive-cb' ),
				'controls' => static::get_control_config( array(
					Main::PRICE_INCLUDE_CURRENCY_SYMBOL,
					Main::PRICE_SHOW_DECIMALS,
				) ),
				'type'     => 'price',
			),
			'_regular_price'      => array(
				'name'     => __( 'Product regular price', 'thrive-cb' ),
				'controls' => static::get_control_config( array(
					Main::PRICE_ON_SALE_EFFECT,
					Main::PRICE_INCLUDE_CURRENCY_SYMBOL,
					Main::PRICE_SHOW_DECIMALS,
				) ),
				'type'     => 'price',
			),
			'_wc_average_rating'  => array(
				'name'     => __( 'Product average rating', 'thrive-cb' ),
				'controls' => array(),
				'type'     => '',
			),
		);
	}

	/**
	 * Return the full shortcode config for the given shortcode keys.
	 *
	 * @param array $shortcode_keys
	 *
	 * @return array
	 */
	public static function get_control_config( $shortcode_keys ) {
		$config = array();

		foreach ( $shortcode_keys as $shortcode_key ) {
			switch ( $shortcode_key ) {
				case Main::PRICE_ON_SALE_EFFECT:
					$config[ $shortcode_key ] = array(
						'type'  => 'select',
						'label' => __( 'If product is on sale', 'thrive-cb' ),
						'value' => array(
							/* the first option is the default one */
							'strikethrough' => __( 'Strikethrough (e.g.  ̶3̶0̶.̶0̶0̶)', 'thrive-cb' ),
							'fade_n_strike' => __( 'Fade and strikethrough', 'thrive-cb' ),
							'fade'          => __( 'Fade', 'thrive-cb' ),
							'normal'        => __( 'Display as normal', 'thrive-cb' ),
						),
					);
					break;
				case Main::PRICE_INCLUDE_CURRENCY_SYMBOL:
					$config[ $shortcode_key ] = array(
						'type'       => 'checkbox',
						'label'      => __( 'Include currency symbol', 'thrive-cb' ),
						'value'      => true, /* must be checked by default */
						'disable_br' => true,
					);
					break;
				case Main::PRICE_SHOW_DECIMALS:
					$config[ $shortcode_key ] = array(
						'type'       => 'checkbox',
						'label'      => __( 'Show minor units (cents, pence etc.)', 'thrive-cb' ),
						'value'      => true, /* must be checked by default */
						'disable_br' => true,
					);
					break;
				default:
					break;
			}
		}

		return $config;
	}

	/**
	 * Remove the decimals from the price and return it ( but keep the thousand separators )
	 *
	 * @param $price
	 *
	 * @return mixed
	 */
	public static function get_price_without_decimals( $price ) {
		/* remove the thousand separator  1,345,543.56 --> 1345543.56 */
		$price = str_replace( wc_get_price_thousand_separator(), '', $price );

		/* cast to (int) in order to remove the decimals 1345543.56 --> 1345543 */
		$base_price = (int) $price;

		/* add the thousand separator back 1345543 --> 1,345,543 */
		$price = static::get_formatted_price( $base_price, false );

		return $price;
	}

	/**
	 * Dynamic links available in the editor
	 *
	 * @return array
	 */
	public static function get_dynamic_links() {
		return array(
			'WooCommerce' => array(
				'links'     => array(
					array(
						array(
							'name'  => __( 'Add To Cart', 'thrive-cb' ),
							'label' => __( 'Add To Cart', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'add_to_cart',
						),
						array(
							'name'  => __( 'Cart Page', 'thrive-cb' ),
							'label' => __( 'Cart Page', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'cart_url',
						),
						array(
							'name'  => __( 'Shop Page', 'thrive-cb' ),
							'label' => __( 'Shop Page', 'thrive-cb' ),
							'url'   => '',
							'show'  => true,
							'id'    => 'shop_url',
						),
					),
				),
				'shortcode' => Main::LINK_SHORTCODE,
			),
		);
	}
}
