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
	 * Check if a product, possibly with variations, exists in the WC cart.
	 *
	 * @param int $product_id The ID of the product to check in the cart.
	 * @param int $variation_id (optional) The ID of the variation for variable products.
	 *
	 * @return array Refer to class comments for more details
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
		$cart_items   = WC()->cart->get_cart();

		if ( $product->is_type( 'variable' ) ) {
			$variations_ids = array_values( $product->get_children() );
			if ( ! in_array( $variation_id, $variations_ids, true ) ) {
				return array(
					'status'  => false,
					'message' => 'Please insert a valid variation id for ' . $product->get_name(),
				);
			}
		}

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

			if ( $variation_id === $variation_id_in_cart ) {
				return array(
					'status'  => true,
					'message' => 'The product and variation is on cart',
				);
			}
		}

		return array(
			'status'  => false,
			'message' => 'The product is not on cart',
		);
	}

	/**
	 * The function adds a product to WC cart
	 *
	 * @param int $product_id The ID of the product to add to the WC cart.
	 * @param int $quantity (optional) Product quantity to add. Default 1.
	 * @param int $variation_id (optional) The ID of the variation for variable products.
	 *
	 * @return array Refer to class comments for more details
	 */
	public static function add_to_cart( $product_id, $quantity = 1, $variation_id = 0 ) {

		/**
		 * Note: The validation for integer values on $product_id and $variation_id is omitted in this section,
		 * as the is_product_in_cart function is responsible for such checks.
		 *
		 * It is essential to invoke the is_product_in_cart function prior to executing any subsequent code.
		 * This strategic placement ensures that any potential errors related to invalid $product_id or $variation_id
		 * can be promptly identified and handled by the is_product_in_cart function.
		 */
		$is_product_in_cart = self::is_product_in_cart( $product_id, $variation_id );

		if ( $is_product_in_cart['status'] ) {
			return array(
				'status'      => $is_product_in_cart['status'],
				'message'     => $is_product_in_cart['message'],
				'status_code' => 800800, // the product is already added to cart.
			);
		}

		if ( ! is_numeric( $quantity ) ) {
			return array(
				'status'  => false,
				'message' => 'The product quantity must be numeric',
			);
		}

		$product_id   = intval( $product_id );
		$quantity     = intval( $quantity );
		$variation_id = intval( $variation_id );
		$product      = wc_get_product( $product_id );
		$cart         = WC()->cart;

		/**
		 * To enable distinct messages for variable and non-variable products,
		 * the add to WC cart function is executed separately for each product type.
		 * This approach facilitates the customization of return messages based on the product's variability.
		 */
		if ( ! $product->is_type( 'variable' ) ) {
			$result = $cart->add_to_cart( $product_id, $quantity );

			if ( false === $result ) {
				return array(
					'status'  => false,
					'message' => 'Something went wrong while trying to add the product to cart',
				);
			}

			return array(
				'status'  => true,
				'message' => 'The product is added to cart successfully',
			);
		}

		if ( $variation_id ) {
			$result = $cart->add_to_cart( $product_id, $quantity, $variation_id );

			if ( false === $result ) {
				return array(
					'status'  => false,
					'message' => 'Something went wrong while trying to add the variable product to cart',
				);
			}

			return array(
				'status'  => true,
				'message' => 'The variable product is added to cart successfully',
			);
		}

		if ( ! $variation_id ) {
			return array(
				'status'  => false,
				'message' => 'The variable product is missing the variation ID',
			);
		}

		return array(
			'status'  => false,
			'message' => 'Something went wrong, Please try again',
		);
	}

	/**
	 * Retrieves the quantity information based on the provided parameters.
	 *
	 * This function returns either the WC cart quantity or the stock quantity based on the specified user flag.
	 *
	 * @param int    $product_id The ID of the product for which we need to retrieve the quantity.
	 * @param string $flag       A flag that indicates whether the request is for WC cart quantity or stock quantity.
	 * @param int    $variation_id The ID of the variation (if applicable) for which we need to know the quantity.
	 *
	 * @return array Refer to class comments for more details
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
					'status'  => $is_product_in_cart['status'],
					'message' => $is_product_in_cart['message'],
				);
			}

			foreach ( $cart_items as $cart_item ) {
				$product_id_in_cart       = $cart_item['product_id'];
				$product_quantity_in_cart = $cart_item['quantity'];

				if ( $product_id !== $product_id_in_cart ) {
					continue;
				}

				return array(
					'status'        => true,
					'cart_quantity' => $product_quantity_in_cart,
					'message'       => 'The number of quantity added to cart is ' . $product_quantity_in_cart,
				);
			}
		}

		if ( 'stock' === $flag ) {
			/**
			 * To enable distinct messages for variable and non-variable products,
			 * The quantity retrieval function is executed separately for each product type.
			 * This approach facilitates the customization of return messages based on the product's variability.
			 */
			if ( ! $product->is_type( 'variable' ) ) {
				$stock_quantity = $product->get_stock_quantity( 'view' );

				return array(
					'status'         => true,
					'stock_quantity' => $stock_quantity,
					'message'        => 'The products\'s available stock quantity is ' . $stock_quantity,
				);
			}

			if ( $product->is_type( 'variable' ) ) {
				/**
				* The wc_get_product() function is called again in this section.
				* This is necessary because it was initially invoked with the product ID,
				* and now it is invoked with the variation ID.
				*
				* The function returns data specific to variations when a variation ID is provided.
				*/
				$variation_data = wc_get_product( $variation_id );
				$stock_quantity = $variation_data->get_stock_quantity( 'view' );

				return array(
					'status'         => true,
					'stock_quantity' => $stock_quantity,
					'message'        => 'The variations\'s available stock quantity is ' . $stock_quantity,
				);
			}
		}
	}

	/**
	 * Removes a product from the WC cart based on the provided product ID and variation ID.
	 *
	 * Note: The variation ID is not mandatory to remove a product from the WC cart.
	 *
	 * However, for variable products, this function requires the variation ID.
	 * The inclusion of the variation ID ensures that the product exists in the WC cart,
	 * verified by the is_product_in_cart function.
	 *
	 * The is_product_in_cart function, in turn, requires the variation ID,
	 * adding an extra layer of confirmation for the user
	 * before proceeding with the product removal from the WC cart.
	 *
	 * @param int $product_id The ID of the product to be removed from the WC cart.
	 * @param int $variation_id The ID of the product variation (if applicable) for more specific removal.
	 *
	 * @return array Refer to class comments for more details.
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
				'status'  => $is_product_in_cart['status'],
				'message' => $is_product_in_cart['message'],
			);
		}

		$product_id   = intval( $product_id );
		$variation_id = intval( $variation_id );
		$product      = wc_get_product( $product_id );
		$cart         = WC()->cart;
		$cart_items   = $cart->get_cart();
		$result       = false;

		foreach ( $cart_items as $cart_item_key => $cart_item ) {
			$product_id_in_cart = $cart_item['product_id'];

			if ( $product_id !== $product_id_in_cart ) {
				continue;
			}

			if ( $product->is_type( 'variable' ) ) {
				if ( $cart_item['variation_id'] === $variation_id ) {
					$result = $cart->remove_cart_item( $cart_item_key );
				}
			} else {
				$result = $cart->remove_cart_item( $cart_item_key );
			}
		}

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

	/**
	 * Updates the quantity of a product in the WC cart.
	 *
	 * This function modifies the quantity of the specified product in the WC cart.
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
				'status'  => $is_product_in_cart['status'],
				'message' => $is_product_in_cart['message'],
			);
		}

		/**
		 * Note: No need to call the function separately for variable and non-variable product types.
		 *
		 * The default value for $variation_id is set to 0,
		 * causing the get_product_quantity function to disregard the variation ID when it is 0.
		 *
		 * This eliminates the necessity for distinct calls,
		 * streamlining the process for both variable and non-variable products.
		 */
		$stock_quantity = self::get_product_quantity( $product_id, 'stock', $variation_id );
		$cart_quantity  = self::get_product_quantity( $product_id, 'cart', $variation_id );

		$product_id   = intval( $product_id );
		$variation_id = intval( $variation_id );
		$quantity     = intval( $quantity );
		$cart         = WC()->cart;

		if ( false === $stock_quantity['status'] ) {
			return array(
				'status'  => $stock_quantity['status'],
				'message' => $stock_quantity['message'],
			);
		}

		if ( false === $cart_quantity['status'] ) {
			return array(
				'status'  => $stock_quantity['status'],
				'message' => $stock_quantity['message'],
			);
		}

		/**
		* The set_quantity function, by default, removes the product from the WooCommerce cart
		* when the quantity is set to 0. This edge case is explicitly handled here to provide
		* a more specific error message in such scenarios.
		*
		* Please refer to the method description to understand why $variation_id is required
		* for the remove_product_from_cart function.
		*/
		if ( 0 === $quantity ) {
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

		$cart_items = $cart->get_cart();

		foreach ( $cart_items as $cart_item_key => $cart_item ) {
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

	/**
	 * The function register attendee slots in the database
	 *
	 * The function accepts the order id and registers allowed_attendees
	 * and registered_attendees slot in the database
	 *
	 * @param int $order_id The order id.
	 * @return mixed
	 */
	public function register_attendee_slots( $order_id ) {
		$order_id                = intval( $order_id );
		$total_allowed_attendees = get_post_meta( $order_id, 'rcn_ar_total_allowed_tickets', true );

		if ( ! empty( $total_allowed_attendees ) ) {
			return array(
				'status'         => false,
				'attendee_count' => 0,
				'message'        => 'Attendee slots are already generated for this order',
			);
		}

		$registration_status = array(
			'status'         => false,
			'attendee_count' => 0,
			'message'        => '',
		);

		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $item_id => $item ) {
			/** @var WC_Order_Item_Product $item */ // phpcs:ignore
			$product_id  = $item->get_product_id();
			$product_qty = $item->get_quantity();

			$is_registered = $this->register_attendee_slots__helper( $product_id, $order_id, $product_qty );

			if ( $is_registered > 0 ) {
				$registration_status['status']         = true;
				$registration_status['attendee_count'] = $registration_status['attendee_count'] + $is_registered;
				continue;
			}
		}

		if ( true === $registration_status['status'] ) {
			$total_allowed_attendees        = $registration_status['attendee_count'];
			$registration_status['message'] = 'Total ' . $total_allowed_attendees . ' slots generated';

			add_post_meta( $order_id, 'rcn_ar_total_allowed_tickets', $total_allowed_attendees );

			return $registration_status;
		}

		$registration_status['message'] = 'No attendee ticket for R-CON exists in this order';
		return $registration_status;
	}

	/**
	 * The helper function of register_attendee_slots
	 *
	 * This function registered the slot in the database
	 * and return product quantity if registered.
	 * Otherwise, it returns 0.
	 *
	 * @param int $product_id The product id.
	 * @param int $order_id The order id.
	 * @param int $product_qty The product quantity.
	 * @return int
	 */
	private function register_attendee_slots__helper( $product_id, $order_id, $product_qty ) {
		$virtual_attendee    = 27807;
		$conference_attendee = 27806;
		$vip_attendee        = 27805;

		if ( $product_id === $virtual_attendee ) {
			add_post_meta( $order_id, 'allowed_virtual_attendees', $product_qty );
			add_post_meta( $order_id, 'registered_virtual_attendees', 0 );

			return $product_qty;
		}

		if ( $product_id === $conference_attendee ) {
			add_post_meta( $order_id, 'allowed_conference_attendees', $product_qty );
			add_post_meta( $order_id, 'registered_conference_attendees', 0 );

			return $product_qty;
		}

		if ( $product_id === $vip_attendee ) {
			add_post_meta( $order_id, 'allowed_vip_attendees', $product_qty );
			add_post_meta( $order_id, 'registered_vip_attendees', 0 );

			return $product_qty;
		}

		return 0;
	}
}
