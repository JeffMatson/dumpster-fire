<?php

namespace JM_SQL_Query_Mail\Controllers;
use JM_SQL_Query_Mail\SQL_DB;

class Run_Query {

	public $db = false;

	public function __construct() {
		$this->db = SQL_DB::get_instance();
	}

	public function perform_actions( $post ) {
		$query = get_post_meta( $post->ID, 'sql_query', true );
		$query_results = $this->db->get_results( $query );

		if ( ! is_array( $query_results ) ) {
			return;
		}

		foreach ( $query_results as $result ) {
			$this->send_email( $post, $result );
		}

		$this->run_after_query( $post->ID );
	}

	public function run_after_query( $post_id ) {
		$post_query_sql = get_post_meta( $post_id, 'run_after_query' );

		if ( $post_query_sql ) {
			$this->db->query( $post_query_sql );
		}
	}

	public function run_query( $query ) {
		return $this->db->get_results( $query );
	}

	public function replace_tag( $tag, $content, $result ) {
		$content  = '';
		$tag_name = str_replace( array( '{sql:', '}' ), '', $tag );

		if ( is_object( $result ) && property_exists( $result, $tag_name ) ) {
			$replaced_content = $result->$tag_name;
			$content = str_replace( $tag, $replaced_content, $content );
		}

		return $content;
	}

	public function parse_content( $content, $result ) {
		$tag_pattern  = '/\{sql\:.*\}/Um';

		preg_match_all( $tag_pattern, $content, $matches, PREG_SET_ORDER, 0 );

		foreach ( $matches as $match ) {
			if ( is_array( $match[0] ) ) {
				$found = $match[0][0];
			} else {
				$found = $match[0];
			}

			$content = $this->replace_tag( $found, $content, $result );
		}

		return $content;
	}

	public function get_parsed_meta( $post_id, $meta_key, $result ) {
		$meta = get_post_meta( $post_id, $meta_key, true );
		return $this->parse_content( $meta, $result );
	}

	public function send_email( $post, $result ) {
		$parsed_content = $this->parse_content( $post->post_content, $result );

		$to_address     = $this->get_parsed_meta( $post->ID, 'to_address', $result );
		$from_address   = $this->get_parsed_meta( $post->ID, 'from_address', $result );
		$cc             = $this->get_parsed_meta( $post->ID, 'cc', $result );
		$bcc            = $this->get_parsed_meta( $post->ID, 'bcc', $result );
		$subject        = $this->get_parsed_meta( $post->ID, 'subject', $result );

		$headers = array();

		if ( $from_address ) {
			$headers[] = 'From: ' . $from_address;
		}
		if ( $cc ) {
			$headers[] = 'Cc:' . $cc;
		}
		if ( $bcc ) {
			$headers[] = 'Bcc: ' . $bcc;
		}

		wp_mail( $to_address, $subject, $parsed_content, $headers );
	}

}


