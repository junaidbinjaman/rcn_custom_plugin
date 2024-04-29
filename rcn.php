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

// phpcs:disabled

function rcn_ar_url_validator() {
    $page_id = 30440;
    $order_id = $_GET['order-id'];

    $allowed_attendees = get_post_meta( $order_id, 'allowed_attendees', true );
    $registered_attendees = get_post_meta( $order_id, 'registered_attendees', true );
    $allowed_attendees = intval( $allowed_attendees );
    $registered_attendees = intval( $registered_attendees );

    if ( is_admin() ) return;
    if ( ! is_singular() ) return;
    if ( ! is_page( $page_id ) ) return;


    if ( empty( $allowed_attendees ) ) { ?>
        <style>
            .rcn-ar-invalid-url {
                display: block !important;
            }

            .rcn-ar-valid-url {
                display: none !important;
            }
        </style>
        <?php
        return;
    }

    if ($allowed_attendees <= $registered_attendees ) { ?>
        <style>
            .rcn-ar-form {
                display: none !important;
            }

            .rcn-ar-display-form-btn {
                display: none !important;
            }

            .rcn-ar-notification {
                display: block !important;
            }
        </style>
        <?php
        return;
    }

}

add_action( 'wp', 'rcn_ar_url_validator' );


function rcn_ar_add_registration_page_link_into_email($order, $sent_to_admin, $plain_text, $email) {
    $ticket_ids = array( 27806, 27805, 27807 );
    $order_id = $order->get_id();

    foreach ( $order->get_items() as $item_id => $item ) {
        $product_id = $item->get_product_id();
        
        if ( in_array( $product_id, $ticket_ids ) ) {
            echo '<a href="http://localhost:10019/attendee-registration/?order-id='. $order_id . '">Register Attendees</a>'; 
        }
     }

     echo "<h2>Hello, World! Goodbye Problems!</h2>";
}

add_action( 'woocommerce_email_after_order_table', 'rcn_ar_add_registration_page_link_into_email', 10, 4 );


