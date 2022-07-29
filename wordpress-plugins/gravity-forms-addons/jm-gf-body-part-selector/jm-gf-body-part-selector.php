<?php
/**
 * Plugin Name: Gravity Forms Body Part Selector Field
 * Plugin URI: https://jeffmatson.net
 * Description: Adds a field to Gravity Forms for the selection of body parts.
 * Version: 1.0
 * Author: Jeff Matson
 * Author URI: https://jeffmatson.net
 *
 * @package JM_GF_Body_Part_Selector
 */

define( 'JM_GF_BODY_PART_SELECTOR_VERSION', '1.0' );
define( 'JM_GF_BODY_PART_SELECTOR_URL', plugin_dir_url( __FILE__ ) );

add_action( 'gform_loaded', array( 'JM_GF_Body_Part_Selector_Bootstrap', 'load' ), 5 );

/**
 * Undocumented class
 */
class JM_GF_Body_Part_Selector_Bootstrap {

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-bodypartselector.php' );

		GFAddOn::register( 'BodyPartSelector' );
	}

}
