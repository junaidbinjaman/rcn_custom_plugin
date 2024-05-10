<?php
/**
 * The elementor form custom action.
 *
 * The code on this file creates a new custom action on elementor form.
 * The action stores the attendee data into R-CON Attendees post type.
 *
 * @package    Rcn
 * @since v1.2.0
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
class Rcn_Ar_Elementor_Form_Register_Attendee_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base {

	/**
	 * Get action name.
	 *
	 * Retrieve attendee_register action name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'attendee_register';
	}

	/**
	 * Get action label.
	 *
	 * Retrieve Attendee Register action label.
	 *
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'Attendee Register', 'rcn' );
	}

	/**
	 * Run action.
	 *
	 * Save the form data into R-CON Attendees custom post type.
	 *
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record Form input records.
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler The ajax handler.
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

		update_post_meta( $fields['orderid'], "registered_{$fields['tickettypekey']}_attendees", $registered_attendees + 1 );
	}

	/**
	 * Register action controls.
	 *
	 * @param \Elementor\Widget_Base $widget The param comment.
	 */
	public function register_settings_section( $widget ) {}

	/**
	 * On export.
	 *
	 * @param array $element The param comment.
	 */
	public function on_export( $element ) {}
}
