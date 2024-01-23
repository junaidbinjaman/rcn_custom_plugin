<?php
/**
 * The file that defines the class containing vendor package-related methods.
 *
 * @link       https://junaidbinjaman.com
 * @since      1.0.0
 * @package    Rcn
 * @subpackage Rcn/public
 */

/**
 * Vendor package handler methods.
 *
 * This class contains methods that help build the vendor checkout process.
 *
 * @since      1.0.0
 * @package    Rcn
 * @subpackage Rcn/public
 * @author     Junaid Bin Jaman
 * @email      me@junaidbinjaman.com
 */
class Rcn_Vendor_Package_Handler {
	/**
	 * Accepts a product ID and retrieves product information
	 * using the provided product ID.
	 *
	 * @param int $product_id Product ID for which you want more information.
	 * @return array An array containing various information about the product.
	 */
	public static function get_product_information( $product_id ) {
		// Access the product object.
		$product = wc_get_product( $product_id );

		// Retrieve data from the product object.
		$name       = $product->get_name();
		$categories = $product->get_category_ids();

		// Data to return.
		$data = array(
			'title'      => $name,
			'categories' => $categories,
		);
		return $data;
	}

	/**
	 * Checks whether a vendor package exists in the cart.
	 * If it exists, the function returns true; otherwise, it returns false.
	 *
	 * @param array $category_ids Category IDs associated with a product.
	 * @return bool
	 */
	public function is_vendor_package_in_cart( $category_ids ) {
		// Vendor category ID.
		$vendor_category_id = 264;

		/**
		 * Check if the category ID exists in the $category_ids array.
		 * Return true if it exists; otherwise, return false.
		 */
		return in_array( $vendor_category_id, $category_ids, true );
	}

	/**
	 * Ajax action: rcn_vatatc
	 *
	 * This function is executed via Ajax.
	 * It receives a product ID sent by jQuery Ajax and checks
	 * whether the product exists in the shopping cart.
	 * If the product exists, the function echoes 'true'; otherwise, it does nothing.
	 *
	 * @return void
	 */
	public function rcn_ajax_check_product_in_cart() {
		// phpcs:disable
		// Retrieve the product ID sent by jQuery.
		$product_id = sanitize_text_field( $_REQUEST['product_id'] );
		// phpcs:enable

		// Get the cart contents.
		$cart = WC()->cart->get_cart();

		/**
		 * Iterate through the cart contents and check
		 * if the product associated with the provided ID exists in the cart or not.
		 *
		 * If it does, echo 'true'; otherwise, do nothing.
		 */
		foreach ( $cart as $item ) {
			$product_id_in_cart = $item['data']->get_id();
			if ( $product_id_in_cart == $product_id ) {
				echo 'true';
				return;
			}
		}
	}
}
