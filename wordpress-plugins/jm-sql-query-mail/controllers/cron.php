<?php

namespace JM_SQL_Query_Mail\Controllers;

class Cron {

	public function get_scheduled() {
		$args = array(
			'post_type'    => 'sql_query_mail',
			'meta_key'     => 'next_query_time',
			'meta_value'   => time(),
			'meta_compare' => '<',
		);
		return new \WP_Query( $args );
	}

	public function run_cron() {
		$scheduled = $this->get_scheduled();
		foreach ( $scheduled->posts as $scheduled_post ) {
			$cron = new Run_Query;
			$cron->perform_actions( $scheduled_post );
		}
	}

	public function sql_mail_updated( $post_id, $post, $update ) {

		// If this is a revision, bail.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Check the interval set.
		$interval = get_post_meta( $post_id, 'query_interval', true );

		// If an interval is set, set the next query time.
		if ( $interval && is_numeric( $interval ) ) {
			$next_tick = time() + intval( $interval );
			update_post_meta( $post_id, 'next_query_time', $next_tick );
		}
	}
}


