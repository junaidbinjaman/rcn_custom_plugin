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

//phpcs:disabled

add_action(
	'init',
	function () {
		register_nav_menu(
			'mobile-header-menu',
			esc_html__( 'Mobile Header Menu', 'RCN' )
		);

		add_shortcode(
			'advanced_mobile_menu',
			function () {
				$menus   = get_nav_menu_locations();
				$menu_id = $menus['mobile-header-menu'];

				$menu_items = wp_get_nav_menu_items( $menu_id );

				echo '<div class="rcn-parent-menus"><ul>';
				foreach ( $menu_items as $item ) {
					if ( ! $item->menu_item_parent ) {
						handler( $menu_items, $item );
					}
				}
				?>
					</ul>
				</div>
				<?php
			}
		);
	}
);

function handler($menu_items, $item) {
	$child_menus = array();

	foreach ( $menu_items as $menu_item ) {
		if ( $menu_item->menu_item_parent == $item->ID ) {
			array_push( $child_menus, $menu_item );
		}
	}

	if ( empty($child_menus) ) { ?>
		<li class="rcn-nav-menu-item">
			<a href="<?php echo esc_url( $item->url ) ?>"><?php echo esc_html( $item->title ); ?></a>
		</li>
		<?php
		return;
	} ?>

	<li class="rcn-nav-menu-item">
		<div class="rcn-nav-child-menu-arrow">
			<a href="<?php echo esc_url( $item->url ); ?>"><?php echo esc_html( $item->title ); ?></a>
			<span class="dashicons dashicons-arrow-down"></span>
			<span class="dashicons dashicons-arrow-right"></span>
		</div>
		<ul class="rcn-nav-child-menu-wrapper">
			<?php
			foreach ($child_menus as $child_menu) {
				handler($menu_items, $child_menu);
			}
			?>
		</ul>
	</li>
	<?php
}

