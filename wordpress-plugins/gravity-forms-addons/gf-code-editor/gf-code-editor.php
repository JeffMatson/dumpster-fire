<?php
/**
 * Gravity Forms Code Editor
 *
 * @package     GF_Code_Editor
 * @author      Jeff Matson
 * @copyright   2017 Jeff Matson
 * @license     GPL-3.0+
 *
 * @wordpress-plugin
 * Plugin Name: Gravity Forms Code Editor Add-On
 * Plugin URI:  https://jeffmatson.net
 * Description: Adds a code editor field to Gravity Forms.
 * Version:     1.0.0
 * Author:      Jeff Matson
 * Author URI:  https://jeffmatson.net
 * Text Domain: gf-code-editor
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

define( 'GF_CODE_EDITOR_VERSION', '1.0' );

add_action( 'gform_loaded', array( 'GF_Code_Editor_Bootstrap', 'load' ), 5 );

class GF_Code_Editor_Bootstrap {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-code-editor.php' );

		GFAddOn::register( 'GF_Code_Editor' );

	}

}
