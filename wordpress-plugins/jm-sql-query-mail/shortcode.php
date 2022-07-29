<?php

namespace JM_SQL_Query_Mail;

class Shortcode {

	public static function init() {
		add_shortcode( 'sql_query_mail', array( self, 'run_shortcode' ) );
	}

	public static function run_shortcode( $atts ) {
		$post_id = $atts['id'];
		$post = get_post( intval( $post_id ) );

		if ( $post ) {
			$query_controller = new \JM_SQL_Query_Mail\Controllers\Run_Query;
			$query_controller->perform_actions( $post );
		}
	}

}
