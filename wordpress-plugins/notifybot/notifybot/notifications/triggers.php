<?php

namespace NotifyBot\Notifications;

class Triggers {

	private static $_triggers = array();

	protected static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {
		foreach ( glob( plugin_dir_path( __FILE__ ) . 'triggers/*' ) as $filename ) {
			require_once( $filename );
		}
	}

	public static function register( $trigger ) {
		self::$_triggers[ $trigger->id ] = $trigger;
	}

	public function exists( $trigger_id ) {
		if ( isset( self::$_triggers[ $trigger_id ] ) ) {
			return true;
		}
	}

	public function get_trigger( $trigger_id ) {
		if ( isset ( self::$_triggers[ $trigger_id ] ) ) {
			return self::$_triggers[ $trigger_id ];
		}
	}

	public function get_all() {
		return self::$_triggers;
	}

	public function get_conditionals( $trigger_id ) {
		if ( isset ( self::$_triggers[ $trigger_id ] ) ) {
			return self::$_triggers[ $trigger_id ]->local_settings;
		}
	}

	public function get_depends( $depends ) {

		$trigger_array = array();

		foreach ( self::$_triggers as $trigger ) {
			if ( $depends == $trigger->depends_on ) {
				$trigger_array[] = $trigger;
			}
		}

		return $trigger_array;
	}

	public function get_merge_tags() {
		$tag_data = array();

		foreach ( self::$_triggers as $trigger_id => $trigger ) {
			$tag_data[$trigger_id] = $trigger->merge_tags;
		}

		return $tag_data;
	}

}

