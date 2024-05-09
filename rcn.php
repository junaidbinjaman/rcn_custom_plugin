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
 * Version:           1.2.0
 * Author:            Junaid Bin Jaman
 * Author URI:        https://junaidbinjaman.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rcn
 * Domain Path:       /languages
 */

use Automattic\WooCommerce\StoreApi\Routes\V1\CartItems;

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

require_once plugin_dir_path( __FILE__ ) . 'test.php';

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

// phpcs:disable

function foobar__callback() {
	// $allowed_attendees    = get_post_meta( 30916, 'allowed_conference_attendees', true );
	// $registered_attendees = get_post_meta( 30916, 'registered_conference_attendees', true );
	// $allowed_attendees    = intval( $allowed_attendees );
	// $registered_attendees = intval( $registered_attendees );

	// echo '<pre>';
	// var_dump( is_page( 30440 ) );
	// echo '</pre>';

	// update_post_meta( 30918, 'allowed_vip_attendees', 2 );
	// delete_post_meta( 30916, 'registered_virtual_attendees' );
}

if( ! is_admin() ) {
	add_action( 'wp', 'foobar__callback' );
}

function rcn_ar_handles_admin_dashboard_widgets() {
	wp_add_dashboard_widget(
		'rcn-ar-admin-generate-unique-url',
		'Generate unique URL for attendees',
		'rcn_ar_admin_generate_unique_url__callback'
	);
}

function rcn_ar_admin_generate_unique_url__callback() {
	?>
	<div class="notice notice-success is-dismissible rcn-ar-admin-unique-url-generator-notice-success"></div>
	<div class="notice notice-error is-dismissible rcn-ar-admin-unique-url-generator-notice-error"></div>
	<div class="notice notice-warning is-dismissible rcn-ar-admin-unique-url-generator-notice-warning">d</div>
	<table class="form-table rcn-ar-admin-unique-url-generator-form">
	<tbody>
		<tr>
			<th scope="row"><label for="input_id">Order ID</label></th>
			<td><input name="input_id" type="text" id="input_id" class="regular-text">
			<button class="button button-primary">Generate URL</button>
		</td>			
		</tr>
	</tbody>
</table>
	<?php
}

add_action( 'wp_dashboard_setup', 'rcn_ar_handles_admin_dashboard_widgets' );

function my_post_like()
{
  echo "asdsa";
  exit;
}

add_action( 'wp_ajax_my_post_like', 'my_post_like' );
add_action( 'wp_ajax_nopriv_my_post_like', 'my_post_like' );

