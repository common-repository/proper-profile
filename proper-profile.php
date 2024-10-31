<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://properprofile.com
 * @since             1.0.0
 * @package           Proper_Profile
 *
 * @wordpress-plugin
 * Plugin Name:       Proper Profile
 * Plugin URI:        http://example.com/proper-profile-uri/
 * Description:       Automagically gets all available public data on your customers, so you can make better informed decisions.
 * Version:           1.1.13
 * Author:            Proper Profile
 * Author URI:        https://properprofile.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       proper-profile
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PROPER_PROFILE_VERSION', '1.1.13' );
define( 'PROPER_PROFILE_DB_VERSION', '1.1.6' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-proper-profile-activator.php
 */
function activate_proper_profile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-proper-profile-activator.php';
	Proper_Profile_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-proper-profile-deactivator.php
 */
function deactivate_proper_profile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-proper-profile-deactivator.php';
	Proper_Profile_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_proper_profile' );
register_deactivation_hook( __FILE__, 'deactivate_proper_profile' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-proper-profile.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_proper_profile() {

	$plugin = new Proper_Profile();
	$plugin->run();

}
run_proper_profile();
