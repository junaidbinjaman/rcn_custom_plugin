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
	 * The orders that has yet available slots to register attendees
	 *
	 * @return void
	 */
	public function order_listings_with_available_slots() {
		$unregistered_attendee_data = $this->rcn_utility->rcon_dashboard();
		$unregistered_attendee_no   = $unregistered_attendee_data['unregistered_attendee_order_data'];
		?>
		<div class="rcon-dashboard-wrapper-detail">
			<h2>Total Order <?php echo esc_html( count( $unregistered_attendee_no ) ); ?></h2>
			<p>The orders that has yet available attendee slots to register.</p>
			<form>
				<?php $this->unregistered_order_list_column( $unregistered_attendee_no ); ?>
			</form>
			<div class="rcon-unregistered-attendee-reminder">
				<span class="dashicons dashicons-edit"></span>
			</div>
			<div class="modal">
				<span class="dashicons dashicons-no-alt rcon-close-modal-btn"></span>
				<div class="rcon-selected-order-reminder-email-instruction">
					<h2>Email Reminder</h2>
					<p>Send an email reminder to all billing email addresses for orders with available slots for attendee registration.</p>
					<p class="email-count"></p>
					<p class="shortcode_guides">
						<strong>Short codes</strong> <br />
						<code>{order_id}</code>: Order ID. <br />
						<code>{first_name}</code>: Billing first name. <br />
						<code>{last_name}</code>: Billing last name. <br />
						<code>{email_address}</code>: Billing email address. <br />
						<code>{br}</code>: The line break. <br />
					</p>
				</div>
				<form class="rcon-selected-order-reminder-email-form">
					<p>
						<label for="email-subject">Subject</label><br />
						<input type="text" name="email-subject" id="email-subject">
					</p>
					<p>
						<label for="email-body">Email Body</label><br />
						<textarea name="email-body" id="email-body"></textarea>
					</p>
					<p>
						<a href="#" class="button button-secondary">Send Reminder</a>
					</p>
					<div class="notification">
						<p class="sending">Sending emails. Please wait...</p>
						<p class="sent">Emails are sent successfully</p>
					</div>
				</form>
			</div>
			<div class="overlay"></div>
		</div>
		<?php
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
			<?php $this->rcon_dashboard_unregistered_attendee_reminder_widget(); ?>
		</div>
		<?php
	}

	/**
	 * Unregister order list colum
	 *
	 * The list contains all the order that has yet available attendee slots
	 *
	 * @param  array $order_list The order list.
	 * @return void
	 */
	public function unregistered_order_list_column( $order_list ) {
		foreach ( $order_list as $order ) {
			$order_id                   = $order->get_ID();
			$unregistered_attendees_num = 0;
			$total_ticket_purchased     = intval( get_post_meta( $order_id, 'rcn_ar_total_allowed_tickets', true ) );

			$registered_virtual_attendee        = intval( get_post_meta( $order_id, 'registered_virtual_attendees', true ) );
			$registered_conference_attendee     = intval( get_post_meta( $order_id, 'registered_conference_attendees', true ) );
			$registered_vip_attendees           = intval( get_post_meta( $order_id, 'registered_vip_attendees', true ) );
			$unregistered_attendee_of_the_order = ( $registered_virtual_attendee + $registered_conference_attendee + $registered_vip_attendees );

			$unregistered_attendees_num += $total_ticket_purchased - $unregistered_attendee_of_the_order;
			$order_edit_page_url         = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

			$billing_email_address = $order->get_billing_email();
			$billing_first_name    = $order->get_billing_first_name();
			$billing_last_name     = $order->get_billing_last_name();
			?>
			<div class="r-con-dashboard-unregistered-attendee-list">
				<div class="checkbox-wrapper">
					<input
					type="checkbox"
					data-billing-email="<?php echo esc_attr( $billing_email_address ); ?>"
					data-order-id="<?php echo esc_attr( $order_id ); ?>"
					>
				</div>
				<p>#<?php echo esc_html( $order_id ); ?></p>
				<p>
					<?php
					echo esc_html( $billing_first_name );
					echo '&nbsp;';
					echo esc_html( $billing_last_name );
					?>
				</p>
				<p>Available slots <strong><?php echo esc_html( $unregistered_attendees_num ); ?></strong></p>
				<p><a href="<?php echo esc_url( $order_edit_page_url ); ?>">View More</a></p>
			</div>
			<?php
		}
	}

	/**
	 * The widget shows the number of total unregistered attendees
	 *
	 * @return void
	 */
	private function rcon_dashboard_total_unregistered_attendees_widget() {
		$unregistered_attendee_data               = $this->rcn_utility->rcon_dashboard();
		$orders_with_available_slots_listing_page = admin_url( 'index.php?page=order-listings' );
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
				<h3><span class="dashicons dashicons-admin-users"></span>
					<?php echo esc_html( $unregistered_attendee_data['num_of_unregistered_attendees'] ); ?>
				</h3>
			</div>
			<div class="footer">
				<a href="<?php echo esc_url( $orders_with_available_slots_listing_page ); ?>">
				<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
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
		$attendee_listing_url = admin_url( '/edit.php?post_type=r-con-attendees' );
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
				<h3><span class="dashicons dashicons-admin-users"></span>
					<?php echo esc_html( $this->found_registered_attendees_num() ); ?>
				</h3>
			</div>
			<div class="footer">
				<a href="<?php echo esc_url( $attendee_listing_url ); ?>">
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * The function returns the number of attendees that has been already registered.
	 *
	 * @return int
	 */
	private function found_registered_attendees_num() {
		$args = array(
			'post_type' => 'r-con-attendees',
		);

		$registered_rcon_attendees = new WP_Query( $args );

		return $registered_rcon_attendees->found_posts;
	}

	/**
	 * The widget shows the number of total registered attendees
	 *
	 * @return void
	 */
	private function rcon_dashboard_total_attendees_widget() {
		$unregistered_attendee_data = $this->rcn_utility->rcon_dashboard();
		$registered_attendee_no     = intval( $this->found_registered_attendees_num() );
		$unregistered_attendee_no   = intval( $unregistered_attendee_data['num_of_unregistered_attendees'] );

		$total_attendees = $registered_attendee_no + $unregistered_attendee_no;
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
				<h3><span class="dashicons dashicons-admin-users"></span>
				<?php echo esc_html( $total_attendees ); ?>
				</h3>
			</div>
			<div class="footer">
				<br />
			</div>
		</div>
		<?php
	}

	/**
	 * The reminder email to all the unregistered attendees.
	 *
	 * @return void
	 */
	private function rcon_dashboard_unregistered_attendee_reminder_widget() {
		$unregistered_attendee_data = $this->rcn_utility->rcon_dashboard();
		$unregistered_attendee_no   = $unregistered_attendee_data['unregistered_attendee_order_data'];

		$order_listing_page_url = admin_url( '/index.php?page=order-listings' );
		?>
		<div class="rcon-dashboard-unregistered-attendee-email-reminder">
			<div class="header">
				<h2>Email Reminder</h2>
				<p>Send an email reminder to all billing email addresses for orders with available slots for attendee registration.</p>
				<p>The reminder will be sent to <?php echo esc_html( count( $unregistered_attendee_no ) ); ?> email</p>
				<p class="shortcode_guides">
					<strong>Short codes</strong> <br />
					<code>{order_id}</code>: Order ID. <br />
					<code>{first_name}</code>: Billing first name. <br />
					<code>{last_name}</code>: Billing last name. <br />
					<code>{email_address}</code>: Billing email address. <br />
					<code>{br}</code>: The line break. <br />
				</p>
			</div>
			<form class="body">
				<p>
					<label for="email-subject">Subject</label><br />
					<input type="text" name="email-subject" id="email-subject">
				</p>
				<p>
					<label for="email-body">Email Body</label><br />
					<textarea name="email-body" id="email-body"></textarea>
				</p>
				<p>
					<a href="#" onclick="rconAttendeeRegisterReminderHandler()"  class="button button-secondary">Send Reminder</a>
				</p>
				<div class="rcon-dashboard-notification">
					<img src="<?php echo esc_url( get_admin_url() . 'images/spinner-2x.gif' ); ?>" />
				</div>
			</form>
			<div class="footer">
				<a href="<?php echo esc_url( $order_listing_page_url ); ?>">
					<small>Click on the arrow if you want to send reminder to the selected orders only</small>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
			</div>
		</div>
		
		<?php
	}

	/**
	 * Send reminder email to all the orders that has yet available slots
	 * to register new attendees
	 *
	 * @return void
	 */
	public function foobar() {
		$unregistered_attendee_data  = $this->rcn_utility->rcon_dashboard();
		$orders_with_available_slots = $unregistered_attendee_data['unregistered_attendee_order_data'];

		check_ajax_referer( 'rcn_admin_nonce', 'nonce' );

		$email_subject = isset( $_POST['emailSubject'] ) ? sanitize_text_field( wp_unslash( $_POST['emailSubject'] ) ) : false;
		$email_body    = isset( $_POST['emailBody'] ) ? sanitize_text_field( wp_unslash( $_POST['emailBody'] ) ) : false;

		foreach ( $orders_with_available_slots as $order ) {
			$order_id           = $order->get_ID();
			$billing_email      = $order->get_billing_email();
			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name  = $order->get_billing_last_name();
			$line_break         = '<br />';

			$order_id_pattern   = '/\{order_id\}/';
			$first_name_pattern = '/\{first_name\}/';
			$last_name_pattern  = '/\{last_name\}/';
			$email_pattern      = '/\{email_address\}/';
			$line_break_pattern = '/\{br\}/';

			$patterns     = array( $order_id_pattern, $first_name_pattern, $last_name_pattern, $email_pattern, $line_break_pattern );
			$replacements = array( $order_id, $billing_first_name, $billing_last_name, $billing_email, $line_break );

			$email_subject = preg_replace( $patterns, $replacements, $email_subject );
			$email_body    = preg_replace( $patterns, $replacements, $email_body );

			wp_mail( $billing_email, $email_subject, $email_body, array( 'Content-Type: text/html; charset=UTF-8' ) );
			break;
		}

		wp_send_json_success( 'The emails are sent successfully', 200 );
	}

	/**
	 * Send the attendee registration reminder email to the selected orders
	 * that has yet available attendee slots to register
	 *
	 * @return void
	 */
	public function send_reminder_to_selected_order() {
		check_ajax_referer( 'rcn_admin_nonce', 'nonce' );

		$email_subject  = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : null;
		$email_body     = isset( $_POST['emailBody'] ) ? sanitize_text_field( wp_unslash( $_POST['emailBody'] ) ) : null;
		$sanitized_data = array();

		if ( isset( $_POST['selectedOrderData'] ) && is_array( $_POST['selectedOrderData'] ) ) {
			foreach( $_POST['selectedOrderData'] as $order_data ) { //phpcs:ignore
				$sanitized_order_data = array();

				$sanitized_order_data['order_id'] = sanitize_text_field( wp_unslash( $order_data['orderId'] ) );
				$sanitized_order_data['email']    = sanitize_text_field( wp_unslash( $order_data['email'] ) );

				$sanitized_data[] = $sanitized_order_data;
			}
		}

		foreach ( $sanitized_data as $order_data ) {
			$order              = wc_get_order( $order_data['order_id'] );
			$order_id           = $order->get_ID();
			$billing_email      = $order->get_billing_email();
			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name  = $order->get_billing_last_name();
			$line_break         = '<br />';

			$order_id_pattern   = '/\{order_id\}/';
			$first_name_pattern = '/\{first_name\}/';
			$last_name_pattern  = '/\{last_name\}/';
			$email_pattern      = '/\{email_address\}/';
			$line_break_pattern = '/\{br\}/';

			$patterns     = array( $order_id_pattern, $first_name_pattern, $last_name_pattern, $email_pattern, $line_break_pattern );
			$replacements = array( $order_id, $billing_first_name, $billing_last_name, $billing_email, $line_break );

			$email_subject = preg_replace( $patterns, $replacements, $email_subject );
			$email_body    = preg_replace( $patterns, $replacements, $email_body );
			wp_mail( $order_data['email'], $email_subject, $email_body, array( 'Content-Type: text/html; charset=UTF-8' ) );
		}

		wp_send_json_success( 'Data logged successfully.' );
	}

	/**
	 * All custom meta boxes goes inside this function
	 *
	 * @return void
	 */
	public function custom_meta_boxes_init() {
		$current_screen = get_current_screen()->id;

		if ( 'shop_order' === $current_screen && 'woocommerce_shop_wc-order' ) {
			add_meta_box(
				'rcon-attendee-data',
				'R-CON Attendee Data',
				array( $this, 'rcon_attendee_data_callback' ),
				$current_screen,
				'normal',
				'high'
			);
		}
	}

	/**
	 * The widget displays the r-con attendee data
	 * inside shop order edit screen
	 *
	 * @param Object $post The WP post object.
	 * @return void
	 */
	public function rcon_attendee_data_callback( $post ) {
		$order_id = $post->ID;

		$rcn_ar_total_allowed_tickets = get_post_meta( $order_id, 'rcn_ar_total_allowed_tickets', true );

		$allowed_conference_attendees    = get_post_meta( $order_id, 'allowed_conference_attendees', true );
		$registered_conference_attendees = get_post_meta( $order_id, 'registered_conference_attendees', true );
		$allowed_conference_attendees    = empty( $allowed_conference_attendees ) ? 0 : $allowed_conference_attendees;
		$registered_conference_attendees = empty( $registered_conference_attendees ) ? 0 : $registered_conference_attendees;

		$allowed_virtual_attendees    = get_post_meta( $order_id, 'allowed_virtual_attendees', true );
		$registered_virtual_attendees = get_post_meta( $order_id, 'registered_virtual_attendees', true );
		$allowed_virtual_attendees    = empty( $allowed_virtual_attendees ) ? 0 : $allowed_virtual_attendees;
		$registered_virtual_attendees = empty( $registered_virtual_attendees ) ? 0 : $registered_virtual_attendees;

		$allowed_vip_attendees    = get_post_meta( $order_id, 'allowed_vip_attendees', true );
		$registered_vip_attendees = get_post_meta( $order_id, 'registered_vip_attendees', true );
		$allowed_vip_attendees    = empty( $allowed_vip_attendees ) ? 0 : $allowed_vip_attendees;
		$registered_vip_attendees = empty( $registered_vip_attendees ) ? 0 : $registered_vip_attendees;

		?>
		<h2 class="rcon-attendee-ticket-data-container-title">R-CON Attendee Ticket Info</h2>
		<div class="rcon-attendee-ticket-data-container">
			<div class="rcon-attendee-ticket-data-box">
				<p>Total Tickets</p>
				<ul>
					<li><span><?php echo esc_html( $rcn_ar_total_allowed_tickets ); ?></span> <br /> Purchased</li>
					<li><span><?php echo esc_html( 'X' ); ?></span> <br /> Registered</li>
				</ul>
			</div>

			<div class="rcon-attendee-ticket-data-box">
				<p>Conference Attendees</p>
				<ul>
					<li><span><?php echo esc_html( $allowed_conference_attendees ); ?></span> <br /> Purchased</li>
					<li><span><?php echo esc_html( $registered_conference_attendees ); ?></span> <br /> Registered</li>
				</ul>
			</div>

			<div class="rcon-attendee-ticket-data-box">
				<p>Virtual Attendees</p>
				<ul>
					<li><span><?php echo esc_html( $allowed_virtual_attendees ); ?></span> <br /> Purchased</li>
					<li><span><?php echo esc_html( $registered_virtual_attendees ); ?></span> <br /> Registered</li>
				</ul>
			</div>

			<div class="rcon-attendee-ticket-data-box">
				<p>VIP Attendees</p>
				<ul>
					<li><span><?php echo esc_html( $allowed_vip_attendees ); ?></span> <br /> Purchased</li>
					<li><span><?php echo esc_html( $registered_vip_attendees ); ?></span> <br /> Registered</li>
				</ul>
			</div>
		</div>

		<?php $this->rcon_unregistered_attendee_reminder_form(); ?>
		<?php
	}

	/**
	 * The form that send the registration minder
	 *
	 * @return void
	 */
	public function rcon_unregistered_attendee_reminder_form() {
		?>
		<div class="rcon-unregistered-attendee-reminder-form">
			<div class="header">
				<strong>Email Reminder</strong>
				<p>Send an email reminder to all billing email addresses for orders with available slots for attendee registration.</p>
				<p>The reminder will be sent to <?php echo esc_html( '' ); ?> email</p>
				<p class="shortcode-guides">
					<strong>Short codes</strong> <br />
					<code>{order_id}</code>: Order ID. <br />
					<code>{first_name}</code>: Billing first name. <br />
					<code>{last_name}</code>: Billing last name. <br />
					<code>{email_address}</code>: Billing email address. <br />
					<code>{br}</code>: The line break. <br />
				</p>
			</div>
			<form class="body">
				<p>
					<label for="email-subject">Subject</label><br />
					<input type="text" name="email-subject" id="email-subject">
				</p>
				<p>
					<label for="email-body">Email Body</label><br />
					<textarea name="email-body" id="email-body"></textarea>
				</p>
				<p>
					<a href="#" onclick="rconAttendeeRegisterReminderHandler()"  class="button button-secondary">Send Reminder</a>
				</p>
				<div class="notifications">
					<div class="loading">
						<p>Sending email. Please Wait</p>
					</div>

					<div class="success">
						<p>Reminder sent successfully</p>
					</div>

					<div class="error">
						<p>Oops! Looks like, something went wrong. Not to worry but to contact becca</p>
					</div>
				</div>
			</form>
			<div class="footer">
				<a href="<?php echo esc_url( '' ); ?>">
					<small>Click on the arrow if you want to send reminder to the selected orders only</small>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
			</div>
		</div>
		<?php
	}
}
