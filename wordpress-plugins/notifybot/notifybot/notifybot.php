<?php
/*
Plugin Name: NotifyBot
Plugin URI: https://notifybot.io
Description: Easy to configure, yet robust notifications for WordPress
Version: 2016.04.19.0
Author: NotifyBot
Author URI: https://notifybot.io
Text Domain: notifybot
License: GPLv3
*/

if ( version_compare( phpversion(), '5.3.0', '<' ) ) {
    trigger_error( 'NotifyBot requires PHP version 5.3.0 or higher.  Contact your web host for more information on upgrading.', E_USER_ERROR );
    exit();
}

define( 'NB_API_URL', 'https://notifybot.io' );
define( 'NB_CORE', 'NotifyBot' );

// Run 3rd party include requirements
require_once( __DIR__ . '/includes/vendor/autoload.php' );
// Autoload NNotifyBot required content
require_once( __DIR__ . '/autoloader.php' );

use NotifyBot\Core;
use NotifyBot\Models\Queue;

// Get an instance of NotifyBot Core
$nb_core = Core::get_instance();
// Get an instance of the Queue model
$nb_queue = Queue::get_instance();

add_action( 'init', 'nb_init' );

function nb_init() {
	\NotifyBot\Notifications\Triggers::get_instance();
	\NotifyBot\Notifications\Services::get_instance();
}

// Signals that NotifyBot has loaded.  Use to load add-ons.
add_action( 'plugins_loaded', array( $nb_core, 'loaded' ) );

// Do license stuff
add_action( 'admin_init', array( $nb_core, 'updater' ), 0 );
add_action( 'admin_init', array( $nb_core, 'activate_license' ) );
add_action( 'admin_init', array( $nb_core, 'deactivate_license' ) );

add_action( 'init', 'nb_admin_init' );
function nb_admin_init() {
	$nb_core = Core::get_instance();

	// Runs the admin menu
	if ( \NotifyBot\Models\Global_Settings::get_instance()->get_value( 'stealth_mode_active' ) == 'on' ) {

		$allowed_users = \NotifyBot\Models\Global_Settings::get_instance()->get_value( 'stealth_allowed_users' );
		$allowed_users = json_decode( $allowed_users );
		$current_user = wp_get_current_user()->user_login;

		if ( is_array( $allowed_users ) && in_array( $current_user, $allowed_users ) ) {
			add_action( 'admin_menu', array( $nb_core, 'admin_menu' ) );
		} else {
			add_action( 'pre_current_active_plugins', 'nb_stealth_hide_plugin_listing' );
		}
	} else {
		add_action( 'admin_menu', array( $nb_core, 'admin_menu' ) );
	}
}

function nb_stealth_hide_plugin_listing() {
	global $wp_list_table;
	$hidearr = array( 'notifybot/notifybot.php' );
	$plugin_list = $wp_list_table->items;

	foreach ( $plugin_list as $key => $val ) {
		if ( in_array( $key, $hidearr ) ) {
			unset( $wp_list_table->items[$key] );
		}
	}

}

// Adds the custom cron timing
add_filter( 'cron_schedules', array( $nb_queue, 'add_cron_timing' ) );

// Processed the queue when the cron fires
add_action( 'nb_process_queue', array( $nb_queue, 'process_queue' ) );

// Runs activation/deactivation actions
register_activation_hook( __FILE__, array( $nb_core, 'run_activation' ) );
register_deactivation_hook( __FILE__, array( $nb_core, 'run_deactivation' ) );

//if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX )  {
	require_once( __DIR__ . '/ajax.php' );
//}