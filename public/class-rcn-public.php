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

		// 30440 is the attendee registration page id.
		if ( is_page( 30440 ) ) {
			wp_enqueue_script( 'attendee-ticket', plugin_dir_url( __FILE__ ) . 'js/rcn-attendee-ticket.js', array( 'jquery' ), $this->version, 'all' );
		}
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
	public function rcn_ar_action_after_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$ticket_ids = array( 27806, 27805, 27807 );
		$is_added   = get_post_meta( $order_id, 'allowed_attendees', true );

		if ( ! empty( $is_added ) ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {
			/** @var WC_Order_Item_Product $item */ // phpcs:ignore
			$product_id = $item->get_product_id();

			if ( in_array( $product_id, $ticket_ids ) ) {
				$quantity = $item->get_quantity();

				add_post_meta( $order_id, 'allowed_attendees', $quantity );
				add_post_meta( $order_id, 'registered_attendees', 0 );
				$this->rcn_ar_redirect_to_registration_page( $order_id );
				break;
			}
		}
	}

	/**
	 * Redirects to the attendee registration page.
	 *
	 * @param int $order_id The order ID used in the query parameter.
	 */
	private function rcn_ar_redirect_to_registration_page( $order_id ) {
		$registration_url = get_page_link( '30440' );
		wp_safe_redirect( $registration_url . '?order-id=' . $order_id );
		exit;
	}

	/**
	 * Validates the attendee registration page URL based on order-id & attendee limits.
	 *
	 * @since 1.2.0
	 */
	public function ar_is_url_valid() {
		$page_id = 30440;

		if ( is_admin() || ! is_singular() || ! is_page( $page_id ) ) {
			return;
		}

		$order_id = isset( $_GET['order-id'] ) ? intval( $_GET['order-id'] ) : 0;

		$total_allowed_tickets = intval( get_post_meta( $order_id, 'rcn_ar_total_allowed_tickets', true ) );

		if ( 0 === $total_allowed_tickets ) {
			$this->visibility_handler(
				array( 'rcn-ar-valid-url', 'rcn-ar-valid-url' ),
				array( 'rcn-ar-invalid-url' )
			);
		}
	}

	/**
	 * Check Order Items for Ticket Types and Manage Component Visibility
	 *
	 * This function iterates through the order items, examining each for ticket types.
	 * When a ticket type is found, it displays corresponding ticket data on the right sidebar
	 * and invokes the appropriate function to handle component visibility for that ticket type.
	 *
	 * @return void
	 */
	public function ar_check_and_manage_ticket_types() {
		$page_id = 30440;

		if ( is_admin() || ! is_singular() || ! is_page( $page_id ) ) {
			return;
		}

		$order_id   = isset( $_GET['order-id'] ) ? absint( $_GET['order-id'] ) : 0; // phpcs:ignore
		$order      = wc_get_order( $order_id );
		$ticket_ids = array(
			'conference' => 27806,
			'vip'        => 27805,
			'virtual'    => 27807,
		);

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
	 * @param int    $ticket_id The ticket product id.
	 * @return void
	 */
	private function ar_ticket_component_visibility_handler( $ticket_type, $ticket_id ) {
		$this->visibility_handler(
			null,
			array(
				'.rcn-ar-' . $ticket_type . '-ticket-data',
			),
		);

		$allowed_attendees    = get_post_meta( $ticket_id, "allowed_{$ticket_type}_attendees", true );
		$registered_attendees = get_post_meta( $ticket_id, "registered_{$ticket_type}_attendees", true );
		$allowed_attendees    = intval( $allowed_attendees );
		$registered_attendees = intval( $registered_attendees );

		if ( $registered_attendees >= $allowed_attendees ) {
			$this->visibility_handler(
				array( ".rcn-ar-{$ticket_type}-attendee .rcn-ar-registration-form" ),
				array( ".rcn-ar-{$ticket_type}-attendee .rcn-ar-notifications" )
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
	 * Add attendee registration page URL in the order confirmation email.
	 *
	 * This function checks if the order contains specific ticket products and appends a link to the attendee
	 * registration page if applicable.
	 *
	 * @param WC_Order $order The order object.
	 * @param bool     $sent_to_admin True if the email is sent to the admin.
	 * @param bool     $plain_text Whether the email is in plain text.
	 * @param WC_Email $email The email object.
	 */
	public function rcn_ar_add_registration_page_link_into_email( $order, $sent_to_admin, $plain_text, $email ) { // phpcs:ignore
		$ticket_ids = array( 27806, 27805, 27807 );
		$order_id   = $order->get_id();

		foreach ( $order->get_items() as $item ) {
			/** @var WC_Order_Item_Product $item */ // phpcs:ignore
			$product_id = $item->get_product_id();

			if ( in_array( $product_id, $ticket_ids, true ) ) {
				$rcn_ar_page_url = add_query_arg( 'order-id', $order_id, get_page_link( 30440 ) );
				$this->rcn_ar_render_link( $rcn_ar_page_url );
				break;
			}
		}
	}

	/**
	 * Renders the link to the attendee registration page.
	 *
	 * @param string $url The URL to the registration page.
	 */
	private function rcn_ar_render_link( $url ) {
		?>
	<div style="margin-bottom: 50px;">
		<a href="<?php echo esc_url( $url ); ?>"
			style="color: white;
					font-weight: normal;
					text-decoration: underline;
					background: #045cbe;
					padding: 20px;">
			Visit attendee registration page
		</a>
	</div>
		<?php
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
	public function rcn_ar_get_ticket_data__callback( $atts = array(), $content = null, $tag = '' ) {
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
		add_shortcode( 'rcn_ar_get_ticket_data', array( $this, 'rcn_ar_get_ticket_data__callback' ) );
	}
}
