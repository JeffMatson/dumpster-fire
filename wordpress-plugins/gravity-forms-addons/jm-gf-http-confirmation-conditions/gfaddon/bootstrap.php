<?php
/**
 * Contains the JM_GF_HTTP_Confirmation_Conditions\GFAddOn\Bootstrap class.
 *
 * @package JM_GF_HTTP_Confirmation_Conditions\GFAddOn
 * @author  Jeff Matson <jeff@jeffmatson.net>
 */

namespace JM_GF_HTTP_Confirmation_Conditions\GFAddOn;

/**
 * Bootstraps Gravity Forms add-on functionality.
 */
class Bootstrap {

	/**
	 * Add-on loader.
	 *
	 * @return void
	 */
	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		// Register the add-on.
		\GFAddOn::register( 'JM_GF_HTTP_Confirmation_Conditions\\GFAddOn\\HTTP_Confirmation_Conditions' );
	}
}
