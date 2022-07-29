<?php
/*
Plugin Name: SQL Query Mail
Plugin URI: https://jeffmatson.net
Description: Sends emails based on SQL queries.
Version: 1.0
Author: Jeff Matson
Author URI: https://jeffmatson.net
*/

namespace JM_SQL_Query_Mail;

require_once( 'autoloader.php' );
require_once( 'vendor/CMB2/init.php' );

add_action( 'init', array( new Controllers\Cron, 'run_cron' ) );
add_action( 'init', array( new Post_Type, 'init' ), 0 );
add_action( 'init', array( new Shortcode, 'init' ), 0 );
add_action( 'save_post_sql_query_mail', array( new Controllers\Cron, 'sql_mail_updated' ), 10, 3 );

add_action( 'cmb2_render_text_number', array( new Meta_Fields\Text_Number, 'render' ), 10, 5 );
add_action( 'cmb2_render_test_query', array( new Meta_Fields\Query_Preview\Button, 'render' ), 10, 5 );
add_action( 'cmb2_render_query_results', array( new Meta_Fields\Query_Preview\Results, 'render' ), 10, 5 );
add_filter( 'cmb2_sanitize_text_number', array( new Meta_Fields\Text_Number, 'sanitize' ), 10, 2 );

add_action( 'cmb2_admin_init', array( new Meta_Boxes\Options, 'init' ) );
add_action( 'cmb2_admin_init', array( new Meta_Boxes\Mail_Settings, 'init' ) );
add_action( 'cmb2_admin_init', array( new Meta_Boxes\Actions, 'init' ) );
