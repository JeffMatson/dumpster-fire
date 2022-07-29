<?php
/**
 * Initializes the plugin.
 *
 * @link              https://jeffmatson.net
 * @since             1.0.0
 * @package           GF_Entry_Approval
 *
 * @wordpress-plugin
 * Plugin Name:       Gravity Forms Entry Approval
 * Plugin URI:        https://jeffmatson.net
 * Description:       Allows for an entry approval processed to be established in Gravity Forms
 * Version:           1.0.0
 * Author:            Jeff Matson
 * Author URI:        https://jeffmatson.net
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       entry-approval
 * Domain Path:       /languages
 */

namespace GF_Entry_Approval;

// The current version of the add-on.
define( 'GF_ENTRY_APPROVAL_VERSION', '1.0' );
// The path to this file.
define( 'GF_ENTRY_APPROVAL_PATH', basename( __DIR__ ) . '/' . basename( __FILE__ ) );

// The autoloader.
require_once( 'autoloader.php' );

// Initializes the add-on within the Gravity Forms add-on framework.
add_action( 'gform_loaded', array( 'GF_Entry_Approval\\GF_Entry_Approval_Bootstrap', 'load' ), 5 );

// Conditionally displays additional content based on query strings.
add_filter( 'gform_get_form_filter', array( new Approval, 'maybe_display_approval_prompt' ), 10, 2 );

// Adds additional notification events based on approval status.
add_filter( 'gform_notification_events', array( new Notifications, 'send_on_approval' ) );

// Creates the custom {entry_approval_url} merge tag.
add_filter( 'gform_replace_merge_tags', array( new Merge_Tags, 'entry_approval_url' ), 10, 7 );

/**
 * Bootstraps the add-on.
 *
 * @package GF_Entry_Approval
 */
class GF_Entry_Approval_Bootstrap {

	/**
	 * If the Payment Add-On Framework exists, Stripe Add-On is loaded.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @uses GFAddOn::register()
	 *
	 * @return void
	 */
	public static function load() {

		// Ensure the add-on framework exists.
		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		// Register the add-on with the add-on framework.
		\GFAddOn::register( 'GF_Entry_Approval\\Feed' );

	}

}
