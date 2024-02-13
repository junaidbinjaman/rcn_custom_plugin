<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://junaidbinjaman.com
 * @since             1.0.0
 * @package           Rcn
 *
 * @wordpress-plugin
 * Plugin Name:       RCN Custom Plugin
 * Plugin URI:        https://realitycapturenetwork.com
 * Description:       Introducing the RCN-exclusive plugin, meticulously crafted with tailored RCN custom codes and specialized functions to enhance your experience uniquely. Designed exclusively for RCN, this plugin brings a world of customized possibilities to your fingertips.
 * Version:           1.0.0
 * Author:            Junaid Bin Jaman
 * Author URI:        https://junaidbinjaman.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rcn
 * Domain Path:       /languages
 */

use Automattic\WooCommerce\StoreApi\Routes\V1\CartItems;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'RCN_VERSION', '1.0.0' );

/**
 * The main utility functions class.
 *
 * The class contains all the utility functions that we will uses across RCN website.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-rcn-utility.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rcn-activator.php
 */
function activate_rcn() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rcn-activator.php';
	Rcn_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rcn-deactivator.php
 */
function deactivate_rcn() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rcn-deactivator.php';
	Rcn_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rcn' );
register_deactivation_hook( __FILE__, 'deactivate_rcn' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rcn.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rcn() {

	$plugin = new Rcn();
	$plugin->run();
}
run_rcn();

/**
 * Temporary Placement for Vendor Package Ajax Functions
 *
 * These Ajax functions are currently placed here to address issues with the vendor package.
 * When initially placed with the other callbacks, they weren't functioning correctly.
 * A more permanent solution for Ajax callbacks is under consideration.
 *
 * Note: This placement is intended to resolve immediate issues and will be revisited for a cleaner organization.
 */

/**
 * Vendor package table availability checker
 *
 * @return void
 */
function is_table_available() {
	$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0; // phpcs:ignore
	$quantity 	= isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : 0; // phpcs:ignore

	$result = Rcn_Utility::get_product_quantity( $product_id, 'stock', $quantity );

	if ( ! $result['status'] ) {
		echo wp_json_encode(
			array(
				'status'  => false,
				'message' => 'Something went wrong',
			)
		);
		wp_die();
	}

	if ( $result['stock_quantity'] > 0 ) {
		echo wp_json_encode(
			array(
				'status'  => true,
				'message' => 'The table is available',
			)
		);
		wp_die();
	}

	if ( $result['stock_quantity'] <= 0 ) {
		echo wp_json_encode(
			array(
				'status'  => false,
				'message' => 'The table is not available',
			)
		);
		wp_die();
	}
}

add_action( 'wp_ajax_is_table_available', 'is_table_available' );
add_action( 'wp_ajax_nopriv_is_table_available', 'is_table_available' );


/**
 * Handle vendor's table add to cart request
 *
 * We are using a status codes to inform the frontend about table's stock status.
 * 1001 indicates that, the table is available for reservation
 * 1002 indicates that, the table is out of stock.
 *
 * @return void
 */
function rcn_table_add_to_cart_handler() {
	$product_id   = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0; // phpcs:ignore
	$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : 0; // phpcs:ignore
	$product      = wc_get_product( $variation_id );

	$is_table_available = Rcn_Utility::get_product_quantity( $product_id, 'stock', $variation_id );

	if ( $is_table_available['stock_quantity'] < 1 ) {
		echo wp_json_encode(
			array(
				'status'            => false,
				'availability_code' => 1002, // unavailable.
				'message'           => 'The table is already reserved',
			)
		);
		wp_die();
	}

	$result = Rcn_Utility::add_to_cart( $product_id, 1, $variation_id );

	echo wp_json_encode(
		array(
			'status'            => $result['status'],
			'price'             => $product->get_price(),
			'availability_code' => 1001, // available.
			'message'           => $result['message'],
		)
	);

	wp_die();
}

add_action( 'wp_ajax_rcn_table_add_to_cart_handler', 'rcn_table_add_to_cart_handler' );
add_action( 'wp_ajax_nopriv_rcn_table_add_to_cart_handler', 'rcn_table_add_to_cart_handler' );

/**
 * The function loads products for vendor package cart
 *
 * @return void
 */
function rcn_get_vp_products() {
	$vp_category_id = isset( $_POST['categoryID'] ) ? intval( $_POST['categoryID'] ) : 0; // phpcs:ignore

	$vp_product_data = array();

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product      = $cart_item['data'];
		$product_id   = $cart_item['product_id'];
		$variation_id = $cart_item['variation_id'];
		$product_name = $product->get_name();
		$quantity     = $cart_item['quantity'];
		$price        = $product->get_price();

		$product_categories = wp_get_post_terms( $product_id, 'product_cat' );

		foreach ( $product_categories as $category ) {
			if ( $category->term_id === $vp_category_id ) {
				$product_data = array(
					'id'       => $product->is_type( 'variation' ) ? $variation_id : $product_id,
					'name'     => $product_name,
					'quantity' => $quantity,
					'price'    => $price,
				);
				array_push(
					$vp_product_data,
					$product_data
				);
			}
		}
	}

	echo wp_json_encode( $vp_product_data );
	wp_die();
}

add_action( 'wp_ajax_rcn_get_vp_products', 'rcn_get_vp_products' );
add_action( 'wp_ajax_nopriv_rcn_get_vp_products', 'rcn_get_vp_products' );

/**
 * Handles product removal requests from vendor cart
 *
 * @return void
 */
function rcn_vp_remove_product_from_cart() {
	$product_id = isset( $_POST['productID'] ) ? intval( $_POST['productID'] ) : 0; // phpcs:ignore

	$is_variable_product = wc_get_product( $product_id )->get_parent_id();

	if ( $is_variable_product ) {
		$result = Rcn_Utility::remove_product_from_cart( $is_variable_product, $product_id );
	} else {
		$result = Rcn_Utility::remove_product_from_cart( $product_id );
	}

	echo wp_json_encode(
		array(
			'status'       => $result['status'],
			'message'      => $result['message'],
			'product_id'   => $is_variable_product,
			'variation_id' => $product_id,
		)
	);

	wp_die();
}

add_action( 'wp_ajax_rcn_vp_remove_product_from_cart', 'rcn_vp_remove_product_from_cart' );
add_action( 'wp_ajax_nopriv_rcn_vp_remove_product_from_cart', 'rcn_vp_remove_product_from_cart' );

/**
 * Handles add-ons add to cart on vendor package
 *
 * @return void
 */
function rcn_vp_addons_add_to_cart() {
	$product_id = isset( $_POST['productID'] ) ? intval( $_POST['productID'] ) : 0; // phpcs:ignore
	$product    = wc_get_product( $product_id );

	$result = Rcn_Utility::add_to_cart( $product_id );

	$cart_quantity = Rcn_Utility::get_product_quantity( $product_id, 'cart' );
	$cart_quantity = $cart_quantity['cart_quantity'] ? $cart_quantity['cart_quantity'] : 0;
	$name          = $product->get_name();
	$price         = $product->get_price();
	$status_code   = $result['status_code'] ? $result['status_code'] : 0;

	echo wp_json_encode(
		array(
			'status'        => $result['status'],
			'message'       => $result['message'],
			'status_code'   => $status_code,
			'productID'     => $product_id,
			'name'          => $name,
			'cart_quantity' => $cart_quantity,
			'price'         => $price,
		)
	);
	wp_die();
}

add_action( 'wp_ajax_rcn_vp_addons_add_to_cart', 'rcn_vp_addons_add_to_cart' );
add_action( 'wp_ajax_nopriv_rcn_vp_addons_add_to_cart', 'rcn_vp_addons_add_to_cart' );

/**
 * Handles addons status checker for vendor package
 *
 * @return void
 */
function rcn_vp_addons_stock_checker() {
	$product_id = isset( $_POST['productID'] ) ? intval( $_POST['productID'] ) : 0; // phpcs:ignore

	$result = Rcn_Utility::get_product_quantity( $product_id, 'stock' );

	if ( is_wp_error( $result ) ) {
		echo wp_json_encode(
			array(
				'status'  => false,
				'message' => 'Error retrieving product quantity',
			)
		);
		wp_die();
	}

	echo wp_json_encode(
		$result
	);
	wp_die();
}

add_action( 'wp_ajax_rcn_vp_addons_stock_checker', 'rcn_vp_addons_stock_checker' );
add_action( 'wp_ajax_nopriv_rcn_vp_addons_stock_checker', 'rcn_vp_addons_stock_checker' );
