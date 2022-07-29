<?php
/**
 * Plugin Name: Gravity Forms Simple PDF Generator
 * Plugin URI: https://jeffmatson.net
 * Description: Generates a PDF based on field entries.
 * Version: 1.0
 * Author: Jeff Matson
 * Author URI: https://jeffmatson.net
 *
 * @package GF_Simple_PDF
 **/

require_once( 'vendor/autoload.php' );

add_action( 'gform_loaded', array( 'Bootstrap', 'load' ), 5 );

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

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'gf-simple-pdf.php' );

		GFAddOn::register( 'GF_Simple_PDF' );

	}

}

