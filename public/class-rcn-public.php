<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://junaidbinjaman.com
 * @since      1.0.0
 *
 * @package    Rcn
 * @subpackage Rcn/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Rcn
 * @subpackage Rcn/public
 * @author     Junaid Bin Jaman <me@junaidbinjaman.com>
 */
class Rcn_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		require_once plugin_dir_path( __DIR__ ) . 'includes/class-rcn-utility.php';
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public facing area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rcn-public.css', array(), $this->version, 'all' );
	}

	/**
	 * The function enqueue public facing js code file.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rcn-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'rcn_phpObject', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function foobar() {
		// $result = Rcn_Utility::is_product_in_cart( 9471 );
		$result = Rcn_Utility::add_to_cart( 1427, 5, 4501 );
		echo esc_html( $result );
	}

	/**
	 * This function reads the contents of the WooCommerce (WC) cart and checks if any vendor packages exist in the cart.
	 * It returns true if vendor packages exist, otherwise false.
	 *
	 * @since 1.0.0
	 */
	public function read_cart_contents() {

		// Include the vendor package handler class.
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/class-rcn-vendor-package-handler.php';

		// Access the cart contents.
		$cart_items = WC()->cart->get_cart();

		// Loop through the cart items and check if any vendor packages exist.
		foreach ( $cart_items as $cart_item ) {
			// Retrieve the product ID from the cart.
			$product_id = $cart_item['product_id'];

			// Initialize an instance of the Rcn_Vendor_Package_Handler class.
			$vendor_package_handler = new Rcn_Vendor_Package_Handler();

			// Pass the product ID to get associated category IDs.
			$category_ids = $vendor_package_handler->get_product_information( $product_id )['categories'];

			/**
			 * Pass the category IDs to check if a vendor category is associated.
			 * Returns true if a vendor package is associated; otherwise, it returns false.
			 */
			$result = $vendor_package_handler->is_vendor_package_in_cart( $category_ids );
		}

		echo 'Hello, World';
	}
}
