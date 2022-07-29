<?php
/*
Plugin Name: Custom - Disable Choices If Other Selected
Plugin URI: https://jeffmatson.net
Description: Custom plugin to disable choices if "other" is selected.
Version: 0.1
Author: Jeff Matson
Author URI: https://jeffmatson.net
*/

add_action( 'gform_enqueue_scripts_8', 'custom_disable_choices_if_other', 10, 2 );
function custom_disable_choices_if_other() {
    wp_enqueue_script( 'custom_handle_choices', plugins_url( 'handle-choices.js', __FILE__ ) );
}