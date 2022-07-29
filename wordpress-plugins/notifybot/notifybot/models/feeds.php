<?php

namespace NotifyBot\Models;
use NotifyBot\Model;

class Feeds extends Model {

	protected static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function create_table() {
		global $wpdb;

		$this->require_wp_upgrade();
		$collation = $wpdb->get_charset_collate();
		$table = $this->table_name( 'global' );

		$sql = 'CREATE TABLE ' . $table . " (
			id int(11) NOT NULL AUTO_INCREMENT,
			feed_slug tinytext NOT NULL,
			item_datetime datetime NOT NULL,
			item_title mediumtext NOT NULL,
			item_content mediumtext NOT NULL,
			PRIMARY KEY  (id)
		) $collation;";
		dbDelta( $sql );

		update_site_option( 'nb_feeds_table_version', $this->feeds_table_version );
	}

	public function get_all_by_slug( $slug ) {
		global $wpdb;
		$table = $this->table_name( 'feeds' );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE feed_slug = %s", $slug ) );
		return $results;
	}

	public function get_all_feed_slugs() {
		global $wpdb;
		$table = $this->table_name( 'feeds' );

		$slugs = $wpdb->get_results( "SELECT DISTINCT feed_slug FROM $table" );
		return $slugs;
	}

	public function insert_item( $slug, $item_title, $item_content ) {
		global $wpdb;
		$table = $this->table_name( 'feeds' );

		$time = current_time( 'mysql' );

		$wpdb->insert(
			$table,
			array(
				'feed_slug' => $slug,
				'item_title' => $item_title,
				'item_content' => $item_content,
				'item_datetime' => $time
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s'
			)
		);
	}

	public function last_updated( $slug ) {
		global $wpdb;
		$table = $this->table_name( 'feeds' );

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(item_datetime) FROM $table WHERE feed_slug = %s", $slug ) );
		return $result;
	}

}