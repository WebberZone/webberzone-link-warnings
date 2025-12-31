<?php
/**
 * Plugin Name: Better External Links
 * Plugin URI: https://webberzone.com/plugins/better-external-links/
 * Description: Enhances accessibility by warning users when links open in new windows or navigate to external sites.
 * Version: 1.0.0
 * Author: WebberZone
 * Author URI: https://webberzone.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: better-external-links
 * Domain Path: /languages
 *
 * @package WebberZone\Better_External_Links
 */

namespace WebberZone\Better_External_Links;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WZ_BEL_VERSION', '1.0.0' );
define( 'WZ_BEL_PLUGIN_FILE', __FILE__ );
define( 'WZ_BEL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WZ_BEL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WZ_BEL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load the autoloader.
require_once WZ_BEL_PLUGIN_DIR . 'includes/autoloader.php';

/**
 * Main plugin instance.
 *
 * @since 1.0.0
 * @return \WebberZone\Better_External_Links\Main
 */
function wz_bel() {
	return \WebberZone\Better_External_Links\Main::get_instance();
}

// Initialize the plugin.
wz_bel();

// Register the activation hook.
register_activation_hook( WZ_BEL_PLUGIN_FILE, __NAMESPACE__ . '\Admin\Activator::activate' );

// Register the deactivation hook.
register_deactivation_hook( WZ_BEL_PLUGIN_FILE, __NAMESPACE__ . '\Admin\Deactivator::deactivate' );
