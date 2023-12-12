<?php
/**
 * This file contains lists of utility functions that we will use across the RCN website.
 *
 * @link       https://junaidbinjaman.com
 * @since      1.0.0
 *
 * @package    Rcn
 * @subpackage Rcn/includes
 */

/**
 * Utility functions for the RCN website.
 *
 * This class defines a collection of utility functions that run across the RCN website.
 * All utility functions are made public static to allow calling them without initializing the RCN_Utility class.
 *
 * @since      1.0.0
 * @package    Rcn
 * @subpackage Rcn/includes
 * @author     Junaid Bin Jaman <me@junaidbinjaman.com>
 */
class Rcn_Utility {

	/**
	 * Checks if a product, possibly with variations, exists in the shopping cart.
	 *
	 * @param int      $product_id The ID of the product to check in the cart.
	 * @param int|null $variation_id (optional) The ID of the variation for variable products.
	 *
	 * @return bool Returns true if the product (and variation, if applicable) is found in the cart; otherwise, returns false.
	 */
	public static function is_product_in_cart( $product_id, $variation_id = 0 ) {

		// Iterate through the WooCommerce cart.
		foreach ( WC()->cart->get_cart() as $cart_item ) {

			// Extract product and variation IDs from the current cart item.
			$product_id_in_cart   = $cart_item['product_id'];
			$variation_id_in_cart = $cart_item['variation_id'];

			// Check if the product ID in the parameter matches the product ID in the cart; if true, proceed.
			if ( $product_id === $product_id_in_cart ) {

				// If a variation ID is provided in the parameter, check for a match with the variation ID in the cart.
				if ( $variation_id ) {
					// Check if the parameter's variation ID matches the variation ID in the cart; if true, the product with the specific variation exists.
					if ( $variation_id === $variation_id_in_cart ) {
						return true;
					}
				} else {
					// If no variation ID provided, the product exists in the cart without considering variations.
					return true;
				}
			}
		}

		// If no matches found in the loop, return false, indicating that the product (with optional variation) is not in the cart.
		return false;
	}
}
