<?php
/*
Plugin Name: Gravity Forms Post Content Fields
Plugin URI: https://jeffmatson.net
Description: Allows
Version: 1.1
Author: Jeff Matson
Author URI: https://jeffmatson.net
*/

namespace GF_Post_Content_Fields;

define( 'GFPCF_VERSION', '1.1' );
define( 'GFPCF_PATH', basename( __DIR__ ) . '/' . basename( __FILE__ ) );

require_once( __DIR__ . '/autoloader.php' );

if ( class_exists( 'GFForms' ) ) {
	\GFForms::include_addon_framework();
	\GF_Fields::register( new Fields\GF_Field_Post_Editor() );
	$core = new Core;
}
