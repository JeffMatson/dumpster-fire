<?php
/**
 * Plugin Name: GF HTTP Confirmation Conditions
 * Plugin URI: https://jeffmatson.net
 * Description: Allows conditional logic within Gravity Forms confirmations, based on HTTP responses.
 * Version: 0.1
 * Author: Jeff Matson
 * Author URI: https://jeffmatson.net
 *
 * @package JM_GF_HTTP_Confirmation_Conditions
 * @author  Jeff Matson <jeff@jeffmatson.net>
 */

namespace JM_GF_HTTP_Confirmation_Conditions;

require_once( 'autoloader.php' );

define( 'HTTP_CONFIRMATION_CONDITIONS_FILE_PATH', __FILE__ );
define( 'HTTP_CONFIRMATION_CONDITIONS_DIR_PATH', __DIR__ );

add_action( 'gform_loaded', array( 'JM_GF_HTTP_Confirmation_Conditions\\GFAddOn\\Bootstrap', 'load' ), 5 );

function http_confirmation_conditions() {
	return GFAddOn\HTTP_Confirmation_Conditions::get_instance();
}
