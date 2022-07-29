<?php
/*
Plugin Name: Gravity Forms Post Content Fields - Post Types Add-On
Plugin URI: https://jeffmatson.net
Description: Adds post type settings to post content fields.
Version: 1.0.0
Author: Jeff Matson
Author URI: https://jeffmatson.net
*/

namespace GF_Post_Content_Fields_Post_Types;

require_once( __DIR__ . '/autoloader.php' );

if ( class_exists( 'GFForms' ) ) {
	\GFForms::include_addon_framework();
	new Core;
}
