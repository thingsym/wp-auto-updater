<?php
/**
 * Plugin Name: WP Auto Updater
 * Plugin URI:  https://github.com/thingsym/wp-auto-updater
 * Description: This plugin enables automatic updates of WordPress Core, Themes, Plugins and Translations. Version control of WordPress Core makes automatic update more safely.
 * Version:     1.6.1
 * Author:      thingsym
 * Author URI:  https://management.thingslabo.com/
 * License:     GPL2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-auto-updater
 * Domain Path: /languages
 *
 * @package         WP_Auto_Updater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( '__WP_AUTO_UPDATER__', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'inc/class-wp-auto-updater.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-wp-auto-updater-history.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-wp-auto-updater-notification.php';

if ( class_exists( 'WP_Auto_Updater' ) ) {
	new WP_Auto_Updater();
};
