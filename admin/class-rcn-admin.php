<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://junaidbinjaman.com
 * @since      1.0.0
 *
 * @package    Rcn
 * @subpackage Rcn/admin
 */

use ElementorPro\Modules\Forms\Fields\Number;
require_once plugin_dir_path( __DIR__ ) . 'admin/admin-callbacks/rcon-dashboard.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rcn
 * @subpackage Rcn/admin
 * @author     Junaid Bin Jaman <me@junaidbinjaman.com>
 */
class Rcn_Admin {
	use Rcon_Dashboard_Callback;

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
	 * Initializes the RCN Utility function.
	 *
	 * @var object
	 */
	private $rcn_utility;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 * @param      object $rcn_utility The rcn utility class initializer.
	 */
	public function __construct( $plugin_name, $version, $rcn_utility ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->rcn_utility = $rcn_utility;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rcn_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rcn_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/rcn-admin.css',
			array(),
			fileatime( plugin_dir_path( __FILE__ ) . 'css/rcn-admin.css' ),
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rcn_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rcn_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/rcn-admin.js',
			array( 'jquery' ),
			fileatime( plugin_dir_path( __FILE__ ) . 'js/rcn-admin.js' ),
			false
		);

		wp_localize_script(
			$this->plugin_name,
			'wp_ajax',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'rcn_admin_nonce' ),
			)
		);
	}

	/**
	 * Register all rcn custom actions for elementor form
	 *
	 * @param ElementorPro\Modules\Forms\Registrars\Form_Actions_Registrar $form_actions_registrar The registrar to which the action is added.
	 * @return void
	 */
	public function register_elementor_form_custom_actions( $form_actions_registrar ) {
		require_once plugin_dir_path( __DIR__ ) . 'includes/elementor-custom-action-classes/class-rcn-ar-elementor-form-register-attendee-action.php';

		$form_actions_registrar->register( new \Rcn_Ar_Elementor_Form_Register_Attendee_Action() );
	}

	/**
	 * The admin dashboard handler function
	 *
	 * @return void
	 */
	public function ar_admin_dashboard_widgets_handler() {
		wp_add_dashboard_widget(
			'rcn-ar-admin-generate-unique-url',
			'Generate unique URL for attendees',
			array( $this, 'ar_admin_generate_unique_url__callback' )
		);
	}

	/**
	 * Attendee registration input widget callback.
	 *
	 * @return void
	 */
	public function ar_admin_generate_unique_url__callback() {
		// Notice containers for success, error, and warning messages.
		echo '<div class="notice notice-success is-dismissible rcn-ar-admin-unique-url-generator-notice-success"></div>';
		echo '<div class="notice notice-error is-dismissible rcn-ar-admin-unique-url-generator-notice-error"></div>';
		echo '<div class="notice notice-warning is-dismissible rcn-ar-admin-unique-url-generator-notice-warning"></div>';

		// Form for generating a unique URL.
		echo '<div class="input-text-wrap rcn-ar-admin-unique-url-generator-form">';
		echo '<p>Enter the Order ID to generate a unique URL for the attendee registration. This input helps you manage and track order-specific URLs efficiently.</p>';
		echo '<label for="input_id">Order ID:</label> <br />';
		echo '<input name="input_id" type="text" id="input_id" class="regular-text" /> <br />';
		echo '<button type="button" class="button button-primary">Generate URL</button> <br />';
		echo '<small class="rcn-ar-loader">Generating URL....</small>';
		echo '</div>';
	}

	/**
	 * Generate RCON attendee registration page URL for admin.
	 * This is an ajax callback function
	 *
	 * @return void
	 */
	public function ar_admin_url_generator() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rcn_admin_nonce' ) ) {
			echo wp_json_encode(
				array(
					'status'  => false,
					'message' => 'The security check failed',
				)
			);
			exit;
		}

		$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;

		$all_options = get_option( 'options', array() );

		$result                  = $this->rcn_utility->register_attendee_slots( $order_id );
		$ar_registration_page_id = isset( $all_options['registration-page'] ) ? $all_options['registration-page'] : false;

		if ( false === $result['status'] ) {
			echo wp_json_encode(
				array(
					'status'  => 'warning',
					'message' => 'Ticket slots have not been generated. This could be due to the order not containing any tickets, or because the slots have already been generated previously.',
				)
			);
			exit;
		}

		if ( true === $result['status'] ) {
			echo wp_json_encode(
				array(
					'status'  => true,
					'message' => 'The attendee registration URL is ' . get_permalink( $ar_registration_page_id ) . '?order-id=' . $order_id,
				)
			);
			exit;
		}
	}

	/**
	 * The function initializes the admin menu/submenu.
	 *
	 * @return void
	 */
	public function admin_menu_init() {
		/**
		 * R-CON Dashboard for admin.
		 */
		add_submenu_page(
			'index.php',
			__( 'R-CON Dashboard', 'rcn' ),
			__( 'R-CON Dashboard', 'rcn' ),
			'manage_options',
			'r-con-dashboard',
			array( $this, 'rcon_dashboard__callback' ),
		);

		/**
		 * This admin menu page contains a list of all the order
		 * That has yet available attendee slots to register.
		 */

		add_menu_page(
			__( 'Order List', 'rcn' ),
			'',
			'manage_options',
			'order-listings',
			array( $this, 'order_listings_with_available_slots' )
		);
	}

	/**
	 * Callback to initialize custom meta boxes.
	 *
	 * This callback is responsible for adding custom meta boxes across all relevant screens
	 * in the admin interface. It ensures that the necessary meta boxes are registered
	 * and ready for use on the specified admin pages.
	 *
	 * @return void
	 */
	public function custom_meta_boxes_init() {
		$current_screen = get_current_screen()->id;

		/**
		* Adds custom meta boxes to the Shop Order edit page.
		*
		* This meta box displays R-CON attendee data associated with the current order
		* and includes a form to send an R-CON attendee registration reminder to the billing email.
		*
		* @return void
		*/
		if ( 'shop_order' === $current_screen && 'woocommerce_shop_wc-order' ) {
			add_meta_box(
				'rcon-attendee-data',
				'R-CON Attendee Data',
				array( $this, 'rcon_attendee_data_on_shop_order_edit_page' ),
				$current_screen,
				'normal',
				'high'
			);
		}
	}
}
