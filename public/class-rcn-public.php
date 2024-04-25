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

		// 27500 is vendor package page id
		if ( is_single( 27500 ) ) {
			wp_enqueue_style( 'rcn-vendor-package', plugin_dir_url( __FILE__ ) . 'css/rcn-vendor-package.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * The function enqueue public facing js code file.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rcn-public.js', array( 'jquery' ), $this->version, false );

		// 27500 is vendor package page id
		if ( is_single( 27500 ) ) {
			wp_enqueue_script( 'vendor-package', plugin_dir_url( __FILE__ ) . 'js/rcn-vendor-package.js', array( 'jquery' ), $this->version, 'all' );
			wp_localize_script( 'vendor-package', 'wp_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}

		wp_enqueue_script( 'attendee-ticket', plugin_dir_url( __FILE__ ) . 'js/rcn-attendee-ticket.js', array( 'jquery' ), $this->version, 'all' );
		wp_localize_script( 'attendee-ticket', 'wp_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
}
