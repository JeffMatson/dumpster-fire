<?php
/**
 * Handles class autoloading.
 *
 * @package GF_Entry_Approval
 */

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Handles autoloading.
 *
 * @param string $classname The name of the class being called.
 *
 * @return void
 */
function gf_entry_approval_autoloader( $classname ) {
	$class     = str_replace( '\\', DIRECTORY_SEPARATOR, str_replace( '_', '-', strtolower( $classname ) ) );
	$file_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $class . '.php';
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}

// Registers the autoloader.
spl_autoload_register( 'gf_entry_approval_autoloader' );
