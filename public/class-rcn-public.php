<?php
/**
 * The public-facing functionality of the plugin.
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
 * @since 1.0.0
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
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/rcn-public.js',
			array( 'jquery' ),
			fileatime( plugin_dir_path( __FILE__ ) . 'js/rcn-public.js' ),
			false
		);

		// 27500 is vendor package page id
		if ( is_single( 27500 ) ) {
			wp_enqueue_script( 'vendor-package', plugin_dir_url( __FILE__ ) . 'js/rcn-vendor-package.js', array( 'jquery' ), $this->version, 'all' );
			wp_localize_script( 'vendor-package', 'wp_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}

		// Retrieving the attendee registration page id.
		$all_options = get_option( 'options', array() );
		$ar_page_id  = isset( $all_options['registration-page'] ) ? $all_options['registration-page'] : false;

		if ( is_page( $ar_page_id ) ) {
			wp_enqueue_script( 'attendee-ticket', plugin_dir_url( __FILE__ ) . 'js/rcn-attendee-ticket.js', array( 'jquery' ), $this->version, 'all' );
		}
	}

	/**
	 * The function returns attendee functionality data based on parameter
	 *
	 * @param int $ar_page_id page-id The attendee registration page id.
	 * @param int $ar_order_id_parameter order-id-parameter The ticket/wc order id.
	 * @param int $ar_ticket_ids ticket-ids The ticket/wc product ids.
	 * @return array
	 */
	private function ar_data_processor( $ar_page_id = true, $ar_order_id_parameter = true, $ar_ticket_ids = true ) {
		$all_options = get_option( 'options', array() );
		$ar_page_id  = isset( $all_options['registration-page'] ) ? $all_options['registration-page'] : false;

		// Conference attendee ticket ids.
		$conference_attendee = isset( $all_options['conference-attendee'] ) ? $all_options['conference-attendee'] : false;
		$virtual_attendee    = isset( $all_options['virtual-attendee'] ) ? $all_options['virtual-attendee'] : false;
		$vip_attendee        = isset( $all_options['vip-attendee'] ) ? $all_options['vip-attendee'] : false;

		$conference_attendee = intval( $conference_attendee );
		$virtual_attendee    = intval( $virtual_attendee );
		$vip_attendee        = intval( $vip_attendee );

		$page_id            = $ar_page_id;
		$order_id_parameter = 'order-id';
		$ticket_ids         = array(
			'conference' => $conference_attendee,
			'virtual'    => $virtual_attendee,
			'vip'        => $vip_attendee,
		);

		$data = array(
			'page-id'            => $page_id,
			'order-id-parameter' => $order_id_parameter,
			'ticket-ids'         => $ticket_ids,
		);

		if ( ! $ar_page_id ) {
			unset( $data['page-id'] );
		}

		if ( ! $ar_order_id_parameter ) {
			unset( $data['order-id-parameter'] );
		}

		if ( ! $ar_ticket_ids ) {
			unset( $data['ticket-ids'] );
		}

		return $data;
	}

	/**
	 * Handles post-payment actions to register attendees.
	 * Upon successful payment, this function checks if the purchased items include specific ticket IDs.
	 * If yes, and attendees are not yet registered, it registers the attendees and redirects to the registration page.
	 *
	 * @since 1.2.0
	 * @param int $order_id The ID of the order passed by the hook.
	 * @return void
	 */
	public function ar_action_after_payment( $order_id ) {

		$rcn_utility = new Rcn_Utility();
		$result      = $rcn_utility->register_attendee_slots( $order_id, true );

		if ( true === $result['status'] ) {
			$this->ar_redirect_to_registration_page( $order_id );
		}
	}

	/**
	 * Redirects to the attendee registration page.
	 *
	 * @param int $order_id The order ID used in the query parameter.
	 */
	private function ar_redirect_to_registration_page( $order_id ) {
		$nonce            = wp_create_nonce( 'ar-registration-nonce' );
		$ar_data          = $this->ar_data_processor( true, true, false );
		$ar_page_id       = $ar_data['page-id'];
		$order_parameter  = $ar_data['order-id-parameter'];
		$registration_uri = get_page_link( $ar_page_id );

		wp_safe_redirect(
			add_query_arg(
				array(
					$order_parameter => $order_id,
				),
				$registration_uri
			)
		);
		exit;
	}

	/**
	 * Validates the attendee registration page URL based on order-id & attendee limits.
	 *
	 * @since 1.2.0
	 */
	public function ar_is_url_valid() {
		$ar_data            = $this->ar_data_processor( true, true, false );
		$page_id            = $ar_data['page-id'];
		$order_id_parameter = $ar_data['order-id-parameter'];

		if ( is_admin() || ! is_singular() || ! is_page( $page_id ) ) {
			return;
		}

		$order_id = isset( $_GET[$order_id_parameter] ) ? absint( $_GET[$order_id_parameter] ) : 0; // phpcs:ignore

		$total_allowed_tickets = intval( get_post_meta( $order_id, 'rcn_ar_total_allowed_tickets', true ) );

		if ( 0 === $total_allowed_tickets ) {
			$this->visibility_handler(
				array( '.rcn-ar-valid-url' ), // Sections to hide.
				array( '.rcn-ar-invalid-url' ) // Sections to display.
			);
			return;
		}

		$this->ar_check_and_manage_ticket_types( $order_id );
	}

	/**
	 * Check Order Items for Ticket Types and Manage Component Visibility
	 *
	 * This function iterates through the order items, examining each for ticket types.
	 * When a ticket type is found, it displays corresponding ticket data on the right sidebar
	 * and invokes the appropriate function to handle component visibility for that ticket type.
	 *
	 * @param int $order_id The order id.
	 * @return void
	 */
	private function ar_check_and_manage_ticket_types( $order_id ) {
		$ar_data    = $this->ar_data_processor( false, false, true );
		$order      = wc_get_order( $order_id );
		$ticket_ids = $ar_data['ticket-ids'];

		foreach ( $order->get_items() as $item_id => $item ) {
			/** @var WC_Order_Item_Product $item  */ // phpcs:ignore
			$product_id = intval( $item->get_product_id() );

			if ( in_array( $product_id, $ticket_ids, true ) ) {
				$this->ar_ticket_component_visibility_handler(
					array_search( $product_id, $ticket_ids, true ),
					$order_id,
				);
			}
		}
	}

	/**
	 * Toggle components inside a ticket type section.
	 *
	 * @param string $ticket_type The ticket type.
	 * @param int    $order_id The order id.
	 * @return void
	 */
	private function ar_ticket_component_visibility_handler( $ticket_type, $order_id ) {
		$this->visibility_handler(
			null,
			array(
				'.rcn-ar-' . $ticket_type . '-ticket-data',
			),
		);

		$allowed_attendees    = get_post_meta( $order_id, "allowed_{$ticket_type}_attendees", true );
		$registered_attendees = get_post_meta( $order_id, "registered_{$ticket_type}_attendees", true );
		$allowed_attendees    = intval( $allowed_attendees );
		$registered_attendees = intval( $registered_attendees );

		if ( $registered_attendees >= $allowed_attendees ) {
			$this->visibility_handler(
				array( ".rcn-ar-{$ticket_type}-attendee .rcn-ar-registration-form" ), // Hide the form.
				array( ".rcn-ar-{$ticket_type}-attendee .rcn-ar-notifications" ) // Display the notification.
			);
		}
	}


	/**
	 * Handles the page sections visibility.
	 *
	 * @param array $sections_to_hide The class names of the sections that you want to hide.
	 * @param array $sections_to_display The class names of the sections that you want to display.
	 * @since 1.2.0
	 */
	private function visibility_handler( $sections_to_hide, $sections_to_display ) {

		if ( ! empty( $sections_to_hide ) ) {
			echo '<style>';
			foreach ( $sections_to_hide as $section ) {
				echo esc_attr( $section ) . ' { display: none !important; }';
			}
			echo '</style>';
		}

		if ( ! empty( $sections_to_display ) ) {
			echo '<style>';
			foreach ( $sections_to_display as $section ) {
				echo esc_attr( $section ) . ' { display: block !important; }';
			}
			echo '</style>';
		}
	}

	/**
	 * The function returns number of total allowed/registered attendees
	 *
	 * The is a callback function of [rcn_ar_get_ticket_data] shortcode .
	 *
	 * @param array  $atts The shortcode attributes.
	 * @param string $content The content inside the shortcode.
	 * @param string $tag The shortcode tag.
	 * @return int
	 */
	public function ar_get_ticket_data__callback( $atts = array(), $content = null, $tag = '' ) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$param = shortcode_atts(
			array(
				'order_id' => '0',
				'meta'     => 'allowed_attendees',
			),
			$atts,
			$tag
		);

		$ticket_data = get_post_meta( $param['order_id'], $param['meta'], true );

		if ( '0' !== $ticket_data && empty( $ticket_data ) ) {
			$ticket_data = null;
		}

		return $ticket_data;
	}

	/**
	 * This function contains all the frontend shortcode functions.
	 *
	 * @return void
	 */
	public function shortcode_initializer() {
		add_shortcode( 'rcn_ar_get_ticket_data', array( $this, 'ar_get_ticket_data__callback' ) );
		add_shortcode( 'get_url_parameter', array( $this, 'get_url_parameter__callback' ) );
	}

	/**
	 * Retrieve the url parameter value.
	 *
	 * @param array  $atts The array contains all shortcode attributes.
	 * @param mixed  $content The content inside the shortcode opening and closing.
	 * @param string $tag The shortcode tag itself.
	 * @return mixed
	 */
	public function get_url_parameter__callback( $atts = array(), $content, $tag ) {
		$atts = array_change_key_case( $atts, CASE_LOWER );

		$rcn_atts = shortcode_atts(
			array(
				'parameter' => 'order-id',
			),
			$atts,
			$tag
		);

		$url_parameter_value = isset( $_GET[ esc_html( $rcn_atts['parameter'] ) ] ) ? sanitize_text_field( wp_unslash( $_GET[ esc_html( $rcn_atts['parameter'] ) ] ) ) : '';
		return $url_parameter_value;
	}

	/**
	 * Allow HTML email template
	 *
	 * @return string
	 */
	public function set_html_content_type() {
		return 'text/html';
	}
}
