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
}
