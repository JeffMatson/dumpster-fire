<?php
/**
 * Main plugin class autoloader.
 *
 * @package JM_GF_HTTP_Confirmation_Conditions
 * @author  Jeff Matson <jeff@jeffmatson.net>
 */

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Autoloads classes based on filename.
 *
 * @param string $classname The name of the class to autoload.
 *
 * @return void
 */
function http_confirmation_conditions_autoload( $classname ) {
	$class     = str_replace( '\\', DIRECTORY_SEPARATOR, str_replace( '_', '-', strtolower( $classname ) ) );
	$file_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $class . '.php';
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}

// Register the autoloader.
spl_autoload_register( 'http_confirmation_conditions_autoload' );
