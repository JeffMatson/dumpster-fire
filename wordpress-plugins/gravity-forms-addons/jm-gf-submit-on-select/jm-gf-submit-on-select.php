<?php
/**
 * Plugin Name: GF Submit On Select
 * Plugin URI: https://jeffmatson.net
 * Description: Allows a form to be immediately submitted if an option is selected.
 * Version: 1.0.1
 * Author: Jeff Matson
 * Author URI: https://jeffmatson.net
 */

define( 'JM_GF_SUBMIT_ON_SELECT_VERSION', '1.0' );

add_action( 'gform_loaded', array( 'GF_Submit_On_Select_Bootstrap', 'load' ), 5 );

class GF_Submit_On_Select_Bootstrap {
	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}
		require_once( 'gfaddon/gf-submit-on-select.php' );
		GFAddOn::register( 'GF_Submit_On_Select' );
	}
}

function gf_submit_on_select() {
	return GF_Submit_On_Select::get_instance();
}
