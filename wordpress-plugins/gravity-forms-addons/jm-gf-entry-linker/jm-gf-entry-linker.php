<?php
/**
 * Plugin Name: Gravity Forms Entry Linker
 * Plugin URI: https://jeffmatson.net
 * Description: Pre-fills field values based on an entry.
 * Version: 1.0
 * Author: Jeff Matson
 * Author URI: https://jeffmatson.net
 *
 * @package JM_GF_Entry_Linker
 **/

namespace JM_GF_Entry_Linker;

add_action( 'gform_loaded', array( 'JM_GF_Entry_Linker\\Bootstrap', 'load' ), 5 );

/**
 * Bootstraps the add-on.
 */
class Bootstrap {

	/**
	 * Loads the add-on.
	 *
	 * @return void
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'core.php' );

		\GFAddOn::register( 'JM_GF_Entry_Linker\Core' );

	}

}

/**
 * Loads an instance of the plugin core.
 *
 * @return \JM_GF_Entry_linker
 */
function load_core() {
	return Core::get_instance();
}
