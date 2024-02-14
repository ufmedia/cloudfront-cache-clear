<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ufmedia.co.uk
 * @since             1.0.0
 * @package           Cloudfront_Cache_Clear
 *
 * @wordpress-plugin
 * Plugin Name:       CloudFront Cache Clear
 * Description:       This WordPress plugin automatically invalidates a CloudFront cache whenever certain conditions are met.
 * Version:           1.1.3
 * Author:            John Thompson
 * Author URI:        https://ufmedia.co.uk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cloudfront-cache-clear
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CLOUDFRONT_CACHE_CLEAR_VERSION', '1.0.1' );

require_once plugin_dir_path( __FILE__ ) . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cloudfront-cache-clear-activator.php
 */
function activate_cloudfront_cache_clear() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cloudfront-cache-clear-activator.php';
	Cloudfront_Cache_Clear_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cloudfront-cache-clear-deactivator.php
 */
function deactivate_cloudfront_cache_clear() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cloudfront-cache-clear-deactivator.php';
	Cloudfront_Cache_Clear_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cloudfront_cache_clear' );
register_deactivation_hook( __FILE__, 'deactivate_cloudfront_cache_clear' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cloudfront-cache-clear.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cloudfront_cache_clear() {

	$plugin = new Cloudfront_Cache_Clear();
	$plugin->run();

}
run_cloudfront_cache_clear();
