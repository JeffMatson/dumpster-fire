<?php

namespace NotifyBot\Models;
use NotifyBot\Model;

class Notifications extends Model {

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
		$table = $this->table_name( 'notify' );

		$sql = 'CREATE TABLE ' . $table . " (
			id int(11) NOT NULL AUTO_INCREMENT,
			group_id int(11) NOT NULL,
			method tinytext NOT NULL,
			service tinytext NOT NULL,
			event tinytext NOT NULL,
			event_trigger tinytext NOT NULL,
			options mediumtext NOT NULL,
			message mediumtext NOT NULL,
			PRIMARY KEY  (id)
		) $collation;";
		dbDelta( $sql );

		update_site_option( 'nb_notify_table_version', $this->notify_table_version );
	}

	public function get_notification_by_id( $id ) {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		$return = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %s", $id ) );
		return $return;
	}

	public function get_all_by_trigger( $trigger ) {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		$has_trigger = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $table WHERE event_trigger = %s", $trigger) );

		if ( ! $has_trigger ) {
			return false;
		}

		return $has_trigger;
	}

	public function id_exists( $id ) {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		$exists = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $table WHERE id = %d", $id) );

		if ( ! $exists ) {
			return false;
		}

		return true;
	}

	public function group_exists( $id ) {
		global $wpdb;
		$table = $this->table_name( 'groups' );

		$exists = $wpdb->get_results( $wpdb->prepare( "SELECT group_id FROM $table WHERE group_id = %d", $id) );
		if ( ! $exists ) {
			return false;
		}
		return true;
	}

	public function get_all_by_group( $group_id ) {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE group_id = %d", $group_id) );
		if ( $results ) {
			return $results;
		} else {
			return false;
		}
	}

	public function next_id() {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		$current_max_id = $wpdb->get_var( "SELECT MAX(group_id) FROM $table" );

		if ( ! $current_max_id ) {
			$current_max_id = '0';
		}

		$current_max_id = intval($current_max_id);
		$next_id = ++$current_max_id;

		return $next_id;
	}

	public function insert( $group_id = '', $method = '', $service = '', $event = '', $trigger = '', $options = '', $message = '' ) {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		$wpdb->insert($table,
			array(
				'group_id'      => $group_id,
				'method'        => $method,
				'service'       => $service,
				'event'         => $event,
				'event_trigger' => $trigger,
				'options'       => $options,
				'message'       => $message,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	public function update( $group_id = '', $method = '', $service = '', $event = '', $trigger = '', $options = '', $message = '' ) {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		return $wpdb->update(
			$table,
			array(
				'method' => $method,
				'service' => $service,
				'event' => $event,
				'options' => $options,
				'message' => $message,
			),
			array(
				'group_id' => $group_id,
				'event_trigger' => $trigger,
			),
			array(),
			array(
				'%d',
				'%s'
			)
		);
	}

//	public function trigger_exists_in_group($trigger, $group) {
//		global $wpdb;
//		$table = $this->table_name( 'notify' );
//
//		$exists = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $table WHERE group_id = %d AND event_trigger = %s", $group, $trigger) );
//		if ( ! $exists ) {
//			return false;
//		}
//		return true;
//	}

	public function delete( $id = null ) {
		global $wpdb;
		$table = $this->table_name( 'notify' );
		$wpdb->delete( $table, array( 'id' => $id ) );
	}

	public function delete_by_group( $group_id = null ) {
		global $wpdb;
		$table = $this->table_name( 'notify' );
		$wpdb->delete( $table, array( 'group_id' => $group_id ) );
	}

	public function get_all_log_locations() {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		$log_dirs = array();
		$has_logs = $wpdb->get_results( "SELECT options FROM $table WHERE service = 'log-local'" );

		foreach ( $has_logs as $log ) {
			$log_options = json_decode( $log->options );

			if ( property_exists( $log_options->service->required, 'log_dir' ) )
				$log_dirs[] = $log_options->service->required->log_dir;

		}

		return array_unique( $log_dirs );

	}

	public function get_all_rss_slugs() {
		global $wpdb;
		$table = $this->table_name( 'notify' );

		$slugs = array();
		$has_slug = $wpdb->get_results( "SELECT options FROM $table WHERE service = 'rss-local'" );

		foreach ( $has_slug as $slug ) {
			$slug_options = json_decode( $slug->options );

			if ( property_exists( $slug_options->service->required, 'slug' ) )
				$slugs[] = $slug_options->service->required->slug;

		}

		return $slugs;
	}

	public function insert_group( $is_active = true, $title = null ) {

		global $wpdb;

		$table = $this->table_name( 'groups' );

		$results = $wpdb->insert( $table,
			array(
				'is_active' => $is_active,
				'title'     => $title,
			),
			array(
				'%d',
				'%s',
			)
		);

		return array(
			'success' => $results,
			'id'      => $wpdb->insert_id,
		);

	}

	public function update_group( $group_id, $is_active = true, $title = null ) {

		global $wpdb;

		$table = $this->table_name( 'groups' );

		$results = $wpdb->replace( $table,
			array(
				'group_id'  => $group_id,
				'is_active' => $is_active,
				'title'     => $title
			),
			array(
				'%d',
				'%d',
				'%s',
			)
		);

		return array(
			'success' => $results,
			'id'      => $wpdb->insert_id,
		);

	}

	public function trigger_exists_in_group( $group_id, $trigger_id ) {
		global $wpdb;

		$table = $this->table_name( 'triggers' );

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE group_id = %d AND trigger_id = %d", $group_id, $trigger_id ) );

		if( $result ) {
			return true;
		} else {
			return false;
		}

	}

	public function insert_trigger( $trigger_data ) {

		global $wpdb;

		$table = $this->table_name( 'triggers' );

		$results = $wpdb->insert( $table,
			array(
				'trigger_id'        => $trigger_data['trigger_id'],
				'group_id'          => $trigger_data['group_id'],
				'trigger_event'     => $trigger_data['trigger_event'],
				'message'           => $trigger_data['message'],
				'conditional_logic' => $trigger_data['conditional_logic'],
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',

			)
		);

		return array(
			'success' => $results,
			'id'      => $wpdb->insert_id,
		);
	}

	public function update_trigger( $trigger_data ) {

		global $wpdb;

		$table = $this->table_name( 'triggers' );

		$results = $wpdb->update( $table,
			array(
				'trigger_event'     => $trigger_data['trigger_event'],
				'message'           => $trigger_data['message'],
				'conditional_logic' => $trigger_data['conditional_logic'],
			),
			array(
				'trigger_id' => $trigger_data['trigger_id'],
				'group_id'   => $trigger_data['group_id'],
			),
			array(
				'%s',
				'%s',
				'%s',
			)
		);

		return array(
			'success' => $results,
			'id'      => $wpdb->insert_id,
		);
	}

	public function get_group_data( $group_id ) {

		global $wpdb;

		$group_table = $this->table_name( 'groups' );
		$triggers_table = $this->table_name( 'triggers' );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $group_table AS grp LEFT JOIN $triggers_table AS trg ON trg.group_id = grp.group_id WHERE grp.group_id = %d", $group_id) );

		return $results;


	}

}