<?php

namespace NotifyBot\Models;
use NotifyBot\Model;

class Global_Settings extends Model {

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
			option_id tinytext NOT NULL,
			option_value tinytext NOT NULL,
			PRIMARY KEY  (id)
		) $collation;";
		dbDelta( $sql );

		update_site_option( 'nb_global_table_version', $this->global_table_version );
	}

	public function get_value( $key ) {
		global $wpdb;
		$table = $this->table_name( 'global' );

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $table WHERE option_id = %s", $key ) );
		return $result;
	}

	public function value_exists( $key ) {
		global $wpdb;
		$table = $this->table_name( 'global' );

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $table WHERE option_id = %s", $key ) );

		if ( $result !== null ) {
			return true;
		} else {
			return false;
		}
	}

	public function update_value( $key, $value ) {
		global $wpdb;
		$table = $this->table_name( 'global' );

		$wpdb->update(
			$table,
			array(
				'option_id' => $key,
				'option_value' => $value
			),
			array(
				'option_id' => $key
			),
			array(
				'%s',
				'%s'
			),
			array(
				'%s'
			)

		);
	}

	public function set_value( $key, $value ) {
		global $wpdb;
		if ( empty( $value ) ) { return; }
		$table = $this->table_name( 'global' );

		if ( is_array( $value ) )
			$value = json_encode( $value );

		if ( $this->value_exists( $key ) ) {
			$this->update_value( $key, $value );
		} else {
			$wpdb->insert(
				$table,
				array(
					'option_id' => $key,
					'option_value' => $value
				),
				array(
					'%s',
					'%s'
				)
			);
		}


	}

}