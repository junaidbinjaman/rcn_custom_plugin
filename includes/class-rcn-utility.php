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
 * This class defines a collection of utility functions that operate runs the RCN website.
 * All utility functions are declared as public static to enable calling them without initializing the RCN_Utility class.
 *
 * Each method in this class returns an associative array.
 * Explanation of array elements:
 * 1. status: A boolean value indicating whether the method fulfilled its purpose (true) or encountered issues (false).
 * 2. message: A concise message describing the method's status.
 *
 * @since      1.0.0
 * @package    Rcn
 * @subpackage Rcn/includes
 * @author     Junaid Bin Jaman <me@junaidbinjaman.com>
 */
class Rcn_Utility {

	/**
	 * Check if a product, possibly with variations, exists in the shopping cart.
	 *
	 * @param int $product_id The ID of the product to check in the cart.
	 * @param int $variation_id (optional) The ID of the variation for variable products.
	 *
	 * @return array look at class comments to learn more
	 **/
	public static function is_product_in_cart( $product_id, $variation_id = 0 ) {

		if ( ! is_numeric( $product_id ) ) {
			return array(
				'status'  => false,
				'message' => 'The product ID must be numeric',
			);
		}

		if ( ! is_numeric( $variation_id ) ) {
			return array(
				'status'  => false,
				'message' => 'The variation ID must be numeric',
			);
		}

		/**
		 * The intval function retrieves integer values from a string'
		 * and converts the numeric string to an integer.
		 *
		 * I use intval() after checking for numeric values because
		 * if a user inserts an invalid value, I want to catch
		 * and return a corresponding error
		 */
		$product_id   = intval( $product_id );
		$variation_id = intval( $variation_id );
		$product      = wc_get_product( $product_id );

		if ( $product->is_type( 'variable' ) ) {
			$variations_ids = array_values( $product->get_children() );
			if ( ! in_array( $variation_id, $variations_ids, true ) ) {
				return array(
					'status'  => false,
					'message' => 'Please insert a valid variation id for ' . $product->get_name(),
				);
			}
		}

		$cart_items = WC()->cart->get_cart();

		foreach ( $cart_items as $cart_item ) {
			$product_id_in_cart   = $cart_item['product_id'];
			$variation_id_in_cart = $cart_item['variation_id'];

			if ( $product_id !== $product_id_in_cart ) {
				continue;
			}

			if ( ! $product->is_type( 'variable' ) ) {
				return array(
					'status'  => true,
					'message' => 'The product is on cart',
				);
			}

			if ( $variation_id !== $variation_id_in_cart ) {
				return array(
					'status'  => false,
					'message' => 'The variation doesn’t exists on cart',
				);
			}

			return array(
				'status'  => true,
				'message' => 'The product and variation is added to cart',
			);
		}

		return array(
			'status'  => false,
			'message' => 'The product is not added to cart',
		);
	}

	/**
	 * The function adds a product to cart
	 *
	 * @param int $product_id The ID of the product to add to the cart.
	 * @param int $quantity (optional) Product quantity to add. Default 1.
	 * @param int $variation_id (optional) The ID of the variation for variable products.
	 *
	 * @return array look at class comments to learn more
	 */
	public static function add_to_cart( $product_id, $quantity = 1, $variation_id = 0 ) {

		if ( ! is_numeric( $product_id ) ) {
			return array(
				'status'  => false,
				'message' => 'The product ID must be numeric',
			);
		}

		if ( ! is_numeric( $variation_id ) ) {
			return array(
				'status'  => false,
				'message' => 'The variation ID must be numeric',
			);
		}

		/**
		 * The intval function retrieves integer values from a string'
		 * and converts the numeric string to an integer.
		 *
		 * I use intval() after checking for numeric values because
		 * if a user inserts an invalid value, I want to catch
		 * and return a corresponding error
		 */
		$product_id         = intval( $product_id );
		$quantity           = intval( $quantity );
		$variation_id       = intval( $variation_id );
		$product            = wc_get_product( $product_id );
		$is_product_in_cart = self::is_product_in_cart( $product_id, $variation_id );

		if ( $is_product_in_cart['status'] ) {
			return array(
				'status'  => true,
				'message' => 'The product is updated successfully..',
			);
		}

		if ( ! $product->is_type( 'variable' ) ) {
			$result = WC()->cart->add_to_cart( $product_id, $quantity );
			if ( false !== $result ) {
				return array(
					'status'  => true,
					'message' => 'The product is added to cart successfully',
				);
			} else {
				return array(
					'status'  => false,
					'message' => 'Something went wrong while trying to add the product to cart',
				);
			}
		}

		if ( $variation_id ) {
			$result = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
			if ( false !== $result ) {
				return array(
					'status'  => true,
					'message' => 'The variable product is added to cart successfully',
				);
			} else {
				return array(
					'status'  => false,
					'message' => 'Something went wrong while trying to add the variable product to cart',
				);
			}
		}

		return array(
			'status'  => false,
			'message' => 'The variable product is missing the variation ID',
		);
	}

	/**
	 * Retrieves the quantity information based on the provided parameters.
	 *
	 * This function returns either the cart quantity or the stock quantity based on the specified user flag.
	 *
	 * @param int    $product_id The ID of the product for which we need to retrieve the quantity.
	 * @param string $flag       A flag that indicates whether the request is for cart quantity or stock quantity.
	 * @param int    $variation_id The ID of the variation (if applicable) for which we need to know the quantity.
	 *
	 * @return array look at class comments to learn more
	 */
	public static function get_product_quantity( $product_id, $flag, $variation_id = 0 ) {

		if ( ! is_numeric( $product_id ) ) {
			return array(
				'status'  => false,
				'message' => 'The product ID must be numeric',
			);
		}

		if ( ! is_numeric( $variation_id ) ) {
			return array(
				'status'  => false,
				'message' => 'The variation ID must be numeric',
			);
		}

		if ( 'cart' !== $flag && 'stock' !== $flag ) {
			return array(
				'status'  => false,
				'message' => 'The flag must be either “cart” or “stock”',
			);
		}

		/**
		 * The intval function retrieves integer values from a string'
		 * and converts the numeric string to an integer.
		 *
		 * I use intval() after checking for numeric values because
		 * if a user inserts an invalid value, I want to catch
		 * and return a corresponding error
		 */
		$product_id         = intval( $product_id );
		$variation_id       = intval( $variation_id );
		$is_product_in_cart = self::is_product_in_cart( $product_id, $variation_id );
		$product            = wc_get_product( $product_id );

		if ( 'cart' === $flag ) {
			$cart       = WC()->cart;
			$cart_items = $cart->get_cart();

			if ( ! $is_product_in_cart['status'] ) {
				return array(
					'status'  => false,
					'message' => $is_product_in_cart['message'],
				);
			}

			foreach ( $cart_items as $cart_item ) {
				$product_id_in_cart = $cart_item['product_id'];
				$product_quantity   = $cart_item['quantity'];

				if ( $product_id !== $product_id_in_cart ) {
					continue;
				}

				return array(
					'status'        => true,
					'cart_quantity' => $product_quantity,
					'message'       => 'The number of quantity added to cart is ' . $product_quantity,
				);
			}
		}

		if ( 'stock' === $flag ) {
			if ( ! $product->is_type( 'variable' ) ) {
				$stock_quantity = wc_get_product( $product_id );
				$stock_quantity = $stock_quantity->get_stock_quantity( 'view' );

				return array(
					'status'         => true,
					'stock_quantity' => $stock_quantity,
					'message'        => 'The products\'s available stock quantity is ' . $stock_quantity,
				);
			}

			if ( $product->is_type( 'variable' ) ) {
				$stock_quantity = wc_get_product( $variation_id );
				$stock_quantity = $stock_quantity->get_stock_quantity( 'view' );

				return array(
					'status'         => true,
					'stock_quantity' => $stock_quantity,
					'message'        => 'The variations\'s available stock quantity is ' . $stock_quantity,
				);
			}
		}
	}

	/**
	 * Remove a product from the cart based on the provided product and variation IDs.
	 *
	 * @param int $product_id The ID of the product to be removed from the cart.
	 * @param int $variation_id The ID of the product variation (if applicable) for more specific removal.
	 *
	 * @return array look at class comments to learn more.
	 */
	public static function remove_product_from_cart( $product_id, $variation_id = 0 ) {

		/**
		 * The function is_product_in_cart() is checking for valid numeric value
		 * for $product_id and $variation_id
		 *
		 * I am calling the function before executing any other code because
		 * It makes sure that, the $product_id and $variation_id are holding the right values and
		 * It also, makes sure the, the product and variable(if applicable) are in cart
		 */
		$is_product_in_cart = self::is_product_in_cart( $product_id, $variation_id );

		if ( ! $is_product_in_cart['status'] ) {
			return array(
				'status'  => false,
				'message' => $is_product_in_cart['message'],
			);
		}

		$product_id   = intval( $product_id );
		$variation_id = intval( $variation_id );
		$cart         = WC()->cart;

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id_in_cart = $cart_item['product_id'];

			if ( $product_id !== $product_id_in_cart ) {
				continue;
			}

			$result = $cart->remove_cart_item( $cart_item_key );

			if ( ! $result ) {
				return array(
					'status'  => false,
					'message' => 'Something went wrong while trying to remove the product from cart',
				);
			}

			return array(
				'status'  => true,
				'message' => 'The product is removed from cart successfully',
			);
		}
	}

	/**
	 * Updates the quantity of a product in the WooCommerce cart.
	 *
	 * This function modifies the quantity of the specified product in the WooCommerce cart.
	 *
	 * @param int $product_id    The ID of the product to be updated in the cart.
	 * @param int $quantity      The updated quantity for the product in the cart.
	 * @param int $variation_id  The ID of the product variation (if applicable) to ensure it is updated in the cart.
	 *
	 * @return array look at class comments to learn more.
	 */
	public static function update_cart_quantity( $product_id, $quantity, $variation_id = 0 ) {

		if ( ! is_numeric( $quantity ) ) {
			return array(
				'status'  => false,
				'message' => 'Invalid product quantity...',
			);
		}

		$is_product_in_cart = self::is_product_in_cart( $product_id, $variation_id );

		if ( ! $is_product_in_cart['status'] ) {
			return array(
				'status'  => $is_product_in_cart['status'],
				'message' => $is_product_in_cart['message'],
			);
		}

		$stock_quantity = self::get_product_quantity( $product_id, 'stock', $variation_id );
		$cart_quantity  = self::get_product_quantity( $product_id, 'cart', $variation_id );

		$product_id   = intval( $product_id );
		$variation_id = intval( $variation_id );
		$quantity     = intval( $quantity );
		$cart         = WC()->cart;

		if ( false === $stock_quantity['status'] ) {
			return array(
				'status'  => false,
				'message' => $stock_quantity['message'],
			);
		}

		if ( '0' === $quantity ) {
			$is_product_removed = self::remove_product_from_cart( $product_id, $variation_id );
			return array(
				'status'  => $is_product_removed['status'],
				'message' => $is_product_removed['message'],
			);
		}

		if ( $quantity > $stock_quantity['stock_quantity'] ) {
			return array(
				'status'  => false,
				'message' => 'Insufficient products available in stock',
			);
		}

		if ( $quantity === $cart_quantity['cart_quantity'] ) {
			return array(
				'status'  => false,
				'message' => 'Same quantity already on cart',
			);
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id_in_cart = $cart_item['product_id'];

			if ( $product_id !== $product_id_in_cart ) {
				continue;
			}

			$is_updated = $cart->set_quantity( $cart_item_key, $quantity );

			if ( $is_updated ) {
				return array(
					'status'  => true,
					'message' => 'Quantity is updated successfully',
				);
			}

			return array(
				'status'  => false,
				'message' => 'Failed to update quantity in the cart',
			);
		}
	}
}
