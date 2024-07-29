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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
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

		$rcn_utility             = new Rcn_Utility();
		$result                  = $rcn_utility->register_attendee_slots( $order_id );
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
		add_submenu_page(
			'index.php',
			__( 'R-CON Dashboard', 'rcn' ),
			__( 'R-CON Dashboard', 'rcn' ),
			'manage_options',
			'r-con-dashboard',
			array( $this, 'rcon_dashboard__callback' ),
		);
	}

	/**
	 * R-CON Dashboard HTML
	 *
	 * @return void
	 */
	public function rcon_dashboard__callback() {
		?>
		<div class="wrap">
			<h2>R-CON Dashboard</h2>
			<div class="rcon-dashboard-wrapper">
				<?php $this->rcon_dashboard_total_unregistered_attendees_widget(); ?>
				<?php $this->rcon_dashboard_total_registered_attendees_widget(); ?>
				<?php $this->rcon_dashboard_total_attendees_widget(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * The widget shows the number of total unregistered attendees
	 *
	 * @return void
	 */
	private function rcon_dashboard_total_unregistered_attendees_widget() {
		?>
		<div class="r-con-dashboard-unregistered-attendee">
			<div class="header">
				<p>Refreshed 5 hrs ago.</p>
				<div>
					<span class="dashicons dashicons-update"></span>
				</div>
			</div>
			<div class="body">
				<p>Total Unregistered Attendees</p>	
				<h3><span class="dashicons dashicons-admin-users"></span>150</h3>
			</div>
			<div class="footer">
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</div>
		</div>
		<?php
	}

	/**
	 * The widget shows the number of total registered attendees
	 *
	 * @return void
	 */
	private function rcon_dashboard_total_registered_attendees_widget() {
		?>
		<div class="r-con-dashboard-unregistered-attendee">
			<div class="header">
				<p>Refreshed 3 mins ago.</p>
				<div>
					<span class="dashicons dashicons-update"></span>
				</div>
			</div>
			<div class="body">
				<p>Total Registered Attendees</p>	
				<h3><span class="dashicons dashicons-admin-users"></span>350</h3>
			</div>
			<div class="footer">
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</div>
		</div>
		<?php
	}

	/**
	 * The widget shows the number of total registered attendees
	 *
	 * @return void
	 */
	private function rcon_dashboard_total_attendees_widget() {
		?>
		<div class="r-con-dashboard-unregistered-attendee">
			<div class="header">
				<p>Refreshed 26 mins ago.</p>
				<div>
					<span class="dashicons dashicons-update"></span>
				</div>
			</div>
			<div class="body">
				<p>Total Attendees</p>	
				<h3><span class="dashicons dashicons-admin-users"></span>500</h3>
			</div>
			<div class="footer">
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</div>
		</div>
		<?php
	}
}
