<?php
/**
 * The file contains the rcon dashboard callback functions
 *
 * @link https://junaidbinjaman.com
 * @since 2.0.0
 *
 * @package Rcn
 * @subpackage Rcn/admin
 */

/**
 * The trait holds the R-CON dashboard callback methods.
 * It basically make the file short and make the code maintenance easier.
 *
 * @package Rcn
 * @subpackage Rcn/admin
 * @since 2.0.0
 * @author Junaid Bin Jaman <junaid@allnextver.com>
 */
trait Rcon_Dashboard_Callback {
	/**
	 * Utility functions for the RCON Dashboard.
	 *
	 * The following section contains utility functions specifically designed for use within the RCON Dashboard.
	 * These functions are not intended for global use; they are tailored to meet the unique needs of this dashboard.
	 */

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
	 * The form that send the registration minder
	 *
	 * @param string $form_type A flag that tell the system whether to send email to the single person, selected peoples or all.
	 *               Values are: "single", "selected", "all". The second parameter is required is the first parameter values is "single".
	 * @param int    $order_id The woocommerce order id. This parameter is required is the first parameter value is "single".
	 * @param array  $notifications The email sending status notification.
	 * @return void
	 */
	public function rcon_unregistered_attendee_reminder_form( $form_type, $order_id = null, $notifications = array() ) {
		if ( 'single' === $form_type && is_null( $order_id ) ) :
			?>
		<div class="error">
			<p>Oops! Something went wrong. Please contact the developer.</p>
		</div>
			<?php
		endif;

		if ( empty( $notifications ) ) :
			$notifications = array(
				'loading' => 'Sending...',
				'success' => 'Successful.',
				'error'   => 'Oops! Looks like, something went wrong.',
			);
		endif;

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
				<input type="hidden" name="form-type" value="<?php echo esc_attr( $form_type ); ?>">
				<?php
				if ( 'single' === $form_type && ! is_null( $order_id ) ) :
					?>
					<input type="hidden" name="order-id", value="<?php echo esc_html( $order_id ); ?>">
					<?php
					endif;
				?>
				<p>
					<label for="email-subject">Subject</label><br />
					<input type="text" name="email-subject" id="email-subject">
				</p>
				<p>
					<label for="email-body">Email Body</label><br />
					<textarea name="email-body" id="email-body"></textarea>
				</p>
				<p>
					<a type="submit" onclick="rconUnregisteredAttendeeReminder(jQuery)"  class="button button-secondary">Send Reminder</a>
				</p>
				<div class="notifications">
					<div class="rcn-loading">
						<p><?php echo esc_html( $notifications['loading'] ); ?></p>
					</div>

					<div class="rcn-success">
						<p><?php echo esc_html( $notifications['success'] ); ?></p>
					</div>

					<div class="rcn-error">
						<p><?php echo esc_html( $notifications['error'] ); ?></p>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * AJAX callback for the `rconUnregisteredAttendeeReminder` JavaScript function.
	 *
	 * This function sends reminder emails to unregistered R-CON attendees. It can target a single attendee, a selected group,
	 * or all unregistered attendees based on the provided `$order_ids`.
	 *
	 * - If `$order_ids` contains specific order IDs, emails will be sent to the billing email addresses associated with those orders.
	 * - If `$order_ids` is empty, the function will iterate through all orders to identify those with available attendee slots.
	 *   For each such order, it will send a reminder email to the order's billing email address.
	 *
	 * @return void
	 */
	public function handle_unregistered_attendee_reminder_email_sending(): void {
		check_ajax_referer( 'rcn_admin_nonce', 'nonce' );

		$data                = isset( $_POST['data'] ) ? $_POST['data'] : array(); // phpcs:ignore
		$sanitized_order_ids = array();

		$form_type     = isset( $data['formType'] ) ? sanitize_text_field( wp_unslash( $data['formType'] ) ) : null;
		$email_subject = isset( $data['emailSubject'] ) ? sanitize_text_field( wp_unslash( $data['emailSubject'] ) ) : null;
		$email_body    = isset( $data['emailBody'] ) ? sanitize_text_field( wp_unslash( $data['emailBody'] ) ) : null;
		$order_ids     = ( isset( $data['orderIds'] ) && is_array( $data['orderIds'] ) ) ? $data['orderIds'] : null;

		if ( ! is_array( $order_ids ) && 'all' !== $form_type ) {
			wp_send_json_error( 'Something went wrong. Please contact admin.' );
		}

		if ( 'all' !== $form_type ) {
			foreach ( $order_ids as $order_id ) {
				$sanitized_order_id = sanitize_text_field( wp_unslash( $order_id ) );
				array_push( $sanitized_order_ids, $sanitized_order_id );
			}
		}

		if ( empty( $sanitized_order_ids ) ) {
			$sanitized_order_ids = null;
		}

		/**
		 * If $sanitized_order_ids is null, the con_orders_with_available_attendee_slots function will returns all the orders
		 * that has yet available attendee slots to register.
		 */
		$unregistered_attendee_order_data = $this->rcn_utility->rcon_orders_with_available_attendee_slots( $sanitized_order_ids );
		$unregistered_attendee_order_data = $unregistered_attendee_order_data['unregistered_attendee_order_data'];

		foreach ( $unregistered_attendee_order_data as $order ) {
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
			$email_body    = $this->handle_unregistered_attendee_reminder_email_template( $email_body, $order_id );

			wp_mail( $billing_email, $email_subject, $email_body, array( 'Content-Type: text/html; charset=UTF-8' ) );
		}

		wp_send_json_success();
	}

	/**
	 * The email template of unregistered attendee registration reminder email
	 *
	 * @param string $email_body The email body that admin types.
	 * @param int    $order_id The order id.
	 * @return string
	 */
	public function handle_unregistered_attendee_reminder_email_template( $email_body, $order_id ): string {
		$rcon_attendee_registration_page_url = home_url( '/attendee-registration/?order-id=' . $order_id );
		ob_start();
		?>
		<center>
			<div style="background-color: #f5f5f5; padding-top: 25px; padding-bottom: 25px;">
				<div style="
				width: 500px;
				background-color: #ffffff;
				text-align: center;
				border-radius: 8px;
				box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.20);
				">
					<img 
					src="https://realitycapturenetwork.com/wp-content/uploads/2024/07/R-CON-2024_Attendee-Registration-Banner-small.jpg"
					style="border-top-left-radius: 8px; border-top-right-radius: 8px;"
					width="100%"
					alt="Banner">

					<div style="padding: 30px; padding-top: 15px" >
			
						<div>
							<p style="
							font-family: 'montserrat', sans-serif
							"><?php echo $email_body; //phpcs:ignore; ?></p>
							<br />
							<a href="<?php echo esc_url( $rcon_attendee_registration_page_url ); ?>" style="
								padding: 15px 20px;
								background: #006cfa;
								font-family: 'Montserrat', Helvetica, sans-serif !important;
								color: white;
								text-decoration: none;
								font-family: 'Montserrat';
								border-radius: 8px;
								font-weight: 500;
								letter-spacing: 0.3px;
								text-transform: uppercase;
								cursor: pointer;
								"
							>Register Attendees</a>
							<hr  style="border: 0.5px solid #006cfa; margin: 30px auto;" />
							
							<!-- Footer data -->
							<p style="
								color: #000000;
								font-size: 11px;
								font-weight: 400;
								line-height: 16px;
								letter-spacing: 0.3px;
								text-decoration-line: none;
								font-family: 'Montserrat', sans-serif;
							">
								Reality Capture Network<br />
								3405 E Overland Rd #375, Meridian, ID 83642 <br />
								If you have any questions, feel free to contact us <br />at team@realitycapturenetwork.com
							</p>
						</div>
					</div>
				</div>
			</div>
		</center>
		<?php
		return ob_get_clean();
	}

	/**
	 * Main callback functions for the RCON Dashboard.
	 *
	 * Below this line, we begin defining the main callback functions that handle the core operations of the RCON Dashboard.
	 * These functions are responsible for executing the primary logic and processes within the dashboard.
	 */

	/**
	 * R-CON Dashboard HTML
	 *
	 * The callback bellow contains the R-CON dashboard html markup.
	 *
	 * @return void
	 */
	public function rcon_dashboard__callback(): void {
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
	 * The widget shows the number of total unregistered attendees
	 *
	 * @return void
	 */
	private function rcon_dashboard_total_unregistered_attendees_widget(): void {
		$unregistered_attendee_data               = $this->rcn_utility->rcon_orders_with_available_attendee_slots();
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
	private function rcon_dashboard_total_registered_attendees_widget(): void {
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
	 * The widget shows the number of total registered attendees
	 *
	 * @return void
	 */
	private function rcon_dashboard_total_attendees_widget(): void {
		$unregistered_attendee_data = $this->rcn_utility->rcon_orders_with_available_attendee_slots();
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
	 * The section contains a from that send R-CON attendee registration reminder email
	 * to all the billing email associated with orders that has yet available attendee slots to register.
	 *
	 * @return void
	 */
	private function rcon_dashboard_unregistered_attendee_reminder_widget() {
		?>
		<div class="rcon-dashboard-unregistered-attendee-email-reminder">
			<?php $this->rcon_unregistered_attendee_reminder_form( 'all' ); ?>
		</div>
		
		<?php
	}

	/**
	 * Widget to display R-CON attendee data on the Shop Order edit screen.
	 *
	 * This widget integrates with the WooCommerce Shop Order edit screen,
	 * displaying relevant R-CON attendee information within the order details.
	 *
	 * @param WP_Post $post The WordPress post object representing the shop order.
	 * @return void
	 */
	public function rcon_attendee_data_on_shop_order_edit_page( $post ) {
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

		<?php
		$notifications = array(
			'loading' => 'Sending the email...',
			'success' => 'The email is sent successfully.',
			'error'   => 'Something went wrong.',
		);

		$this->rcon_unregistered_attendee_reminder_form( 'single', $order_id, $notifications );
	}

	/**
	 * The orders that has yet available slots to register attendees
	 *
	 * @return void
	 */
	public function order_listings_with_available_slots() {
		$unregistered_attendee_data = $this->rcn_utility->rcon_orders_with_available_attendee_slots();
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
				<!-- Modal close btn -->
				<span class="dashicons dashicons-no-alt rcon-close-modal-btn"></span> 
				<?php $this->rcon_unregistered_attendee_reminder_form( 'selected' ); ?>
			</div>
			<div class="overlay"></div>
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
}
