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
//phpcs:disable

function set_html_content_type() {
    return 'text/html';
}

// Add filter to set the content type to HTML
add_filter('wp_mail_content_type', 'set_html_content_type');

function foobar1() {
	$content = '
	        <div style="
        width: 500px;
        padding: 30px;
        text-align: center;
        border-radius: 8px;
        box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.20);
        ">
            <img
                width="120"
                height="100%"
                src="https://realitycapturenetwork.com/wp-content/uploads/2024/06/RCN_no-wordmark_blue.png"
                alt="Logo"
            >
            <h4 style="
            color: #000;
            font-family: Montserrat;
            font-size: 13px;
            font-style: normal;
            font-weight: 500;
            line-height: normal;
            ">Order ID: #31529</h4>

            <hr  style="border: 0.5px solid #D2D4DE; width: 300px; margin: 20px auto;" />

            <div>
                <h2 style="
                    color: #000;
                    font-family: Montserrat;
                    font-size: 15px;
                    font-style: normal;
                    font-weight: 700;
                    line-height: normal;
                    ">The Title Goes Here</h2>
                <p style="
                    color: #1A1A1A;
                    text-align: center;
                    font-family: Inter;
                    font-size: 14px;
                    font-weight: 400;
                    line-height: normal;
                    font-family: Montserrat;
                    color: #818080;
                ">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Sequi a dolorem, molestias illo ex
                    praesentium cupiditate, necessitatibus voluptatem quas modi culpa assumenda quo provident error nam
                    esse minus eum vero.</p>
                <button style="
                    border-radius: 8px;
                    background: #006CFA;
                    color: var(--White, #FFF);
                    text-align: center;
                    font-family: Montserrat;
                    font-size: 14px;
                    font-style: normal;
                    font-weight: 500;
                    letter-spacing: 0.3px;
                    text-transform: uppercase;
                    border: none;
                    padding: 15px 20px;
                    margin-top: 10px;
                ">VISIT REGISTRATION PAGE</button>
                <hr  style="border: 0.5px solid #D2D4DE; width: 300px; margin: 30px auto;" />
                <p style="
                    color: #98A0A6;
                    font-family: Montserrat;
                    font-size: 11px;
                    font-weight: 400;
                    line-height: 16px;
                    letter-spacing: 0.3px;
                    text-decoration-line: none;
                ">
                    Reality Capture Network, LLC <br />
                    3405 E Overland Rd #375, Meridian, ID 83642 <br />
                    If you have any questions, feel free to contact us at team@realitycapturenetwork.com
            </p>
            </div>
        </div>
	';
	wp_mail('junaid@allnextver.com', 'Testing email', $content);
}

add_action( 'init', 'foobar1' );
