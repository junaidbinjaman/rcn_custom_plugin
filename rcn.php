<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://junaidbinjaman.com
 * @since             1.0.0
 * @package           Rcn
 *
 * @wordpress-plugin
 * Plugin Name:       RCN Custom Plugin
 * Plugin URI:        https://realitycapturenetwork.com
 * Description:       Introducing the RCN-exclusive plugin, meticulously crafted with tailored RCN custom codes and specialized functions to enhance your experience uniquely. Designed exclusively for RCN, this plugin brings a world of customized possibilities to your fingertips.
 * Version:           2.0.0
 * Author:            Junaid Bin Jaman
 * Author URI:        https://junaidbinjaman.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rcn
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'RCN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rcn-activator.php
 */
function activate_rcn() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rcn-activator.php';
	Rcn_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rcn-deactivator.php
 */
function deactivate_rcn() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rcn-deactivator.php';
	Rcn_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rcn' );
register_deactivation_hook( __FILE__, 'deactivate_rcn' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rcn.php';

/**
 * The main utility functions class.
 *
 * The class contains all the utility functions that we will uses across RCN website.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-rcn-utility.php';

/**
 * The ajax callbacks.
 *
 * The file contains all the ajax callbacks those are used across the website.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/rcn-ajax-callbacks.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rcn() {

	$plugin = new Rcn();
	$plugin->run();
}
run_rcn();


add_action( 'wp_ajax_unregistered_attendee_reminder', 'unregistered_attendee_reminder' );
add_action( 'wp_ajax_nopiv_unregistered_attendee_reminder', 'unregistered_attendee_reminder' );

/**
 * Undocumented function
 *
 * @return void
 */
function unregistered_attendee_reminder() {
	check_ajax_referer( 'rcn_admin_nonce', 'nonce' );

	$data                = isset( $_POST['data'] ) ? $_POST['data'] : array();
	$sanitized_order_ids = array();

	$form_type     = isset( $data['formType'] ) ? sanitize_text_field( wp_unslash( $data['formType'] ) ) : null;
	$email_subject = isset( $data['emailSubject'] ) ? sanitize_text_field( wp_unslash( $data['emailSubject'] ) ) : null;
	$email_body    = isset( $data['emailBody'] ) ? sanitize_text_field( wp_unslash( $data['emailBody'] ) ) : null;
	$order_ids     = ( isset( $data['orderIds'] ) && is_array( $data['orderIds'] ) ) ? $data['orderIds'] : null;

	if ( 'all' === $form_type ) :
		$unregistered_attendee_data = $this->rcn_utility->rcon_orders_with_available_attendee_slots();
		$sanitized_order_ids        = $unregistered_attendee_data['unregistered_attendee_order_data'];
	endif;

	if ( ! is_array( $order_ids ) && 'all' !== $form_type ) :
		wp_send_json_error( 'Something went wrong. Please contact admin.' );
	endif;

	if ( 'all' !== $form_type ) :
		foreach ( $order_ids as $order_id ) :
			$sanitized_order_id = sanitize_text_field( wp_unslash( $order_id ) );
			array_push( $sanitized_order_ids, $sanitized_order_id );
		endforeach;
	endif;

	foreach ( $sanitized_order_ids as $order ) {
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

	wp_send_json_success();
}
