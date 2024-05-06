<?php
/**
 * The elementor form custom action.
 *
 * The code on this file creates a new custom action on elementor form.
 * The action stores the attendee data into R-CON Attendee post type.
 *
 * @link       https://junaidbinjaman.com
 * @since      1.0.0
 *
 * @package    Rcn
 * @subpackage Rcn/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor form attendee register action.
 *
 * Custom Elementor form action which will register an attendee.
 *
 * @since 1.0.0
 */
class Rcn_Rcon_Ar extends \ElementorPro\Modules\Forms\Classes\Action_Base {

	/**
	 * Get action name.
	 *
	 * Retrieve ping action name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return 'attendee_register';
	}

	/**
	 * Get action label.
	 *
	 * Retrieve ping action label.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'Attendee Register', 'rcn' );
	}

	/**
	 * Run action.
	 *
	 * Ping an external server after form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record The param comment.
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler The param comment.
	 */
	public function run( $record, $ajax_handler ) {

		// Get submitted form data.
		$raw_fields = $record->get( 'fields' );

		// Normalize form data.
		$fields = array();
		foreach ( $raw_fields as $id => $field ) {
			$fields[ $id ] = $field['value'];
		}

		$registered_attendees = get_post_meta( $fields['orderid'], "registered_{$fields['tickettypekey']}_attendees", true );
		$registered_attendees = intval( $registered_attendees );

		$my_post = array(
			'post_title'  => $fields['firstname'] . ' ' . $fields['lastname'],
			'post_status' => 'publish',
			'post_type'   => 'r-con-attendees',
			'meta_input'  => array(
				'orderid'        => $fields['orderid'],
				'conferencecode' => $fields['conferencecode'],
				'tickettype'     => $fields['tickettype'],
				'firstname'      => $fields['firstname'],
				'lastname'       => $fields['lastname'],
				'phone'          => $fields['phone'],
				'email'          => $fields['email'],
				'jobtitle'       => $fields['jobtitle'],
				'company'        => $fields['company'],
				'address'        => $fields['address'],
				'linkedin'       => $fields['linkedin'],
				'shirtsize'      => $fields['shirtsize'],
				'happyhour'      => $fields['happyhour'],
				'message'        => $fields['message'],
			),
		);

		// Insert the post into the database.
		wp_insert_post( $my_post );

		// update_post_meta( $fields['orderid'], "registered_{$fields['tickettypekey']}_attendees", $registered_attendees + 1 );.
		?>
		<script>
			location.href = 'http://localhost:10019/attendee-registration/?order-id=30916'
		</script>
		<?php
	}

	/**
	 * Register action controls.
	 *
	 * Ping action has no input fields to the form widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \Elementor\Widget_Base $widget The param comment.
	 */
	public function register_settings_section( $widget ) {}

	/**
	 * On export.
	 *
	 * Ping action has no fields to clear when exporting.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $element The param comment.
	 */
	public function on_export( $element ) {}
}
