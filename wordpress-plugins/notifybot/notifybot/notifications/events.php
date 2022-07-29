<?php

namespace NotifyBot\Notifications;

class Events {

	private static $_events = array();

	protected static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {
		foreach ( glob( plugin_dir_path( __FILE__ ) . 'events/*.php' ) as $filename ) {
			require_once( $filename );
		}
	}

	public static function register( $event ) {
		self::$_events[ $event->id ] = $event;
	}

	public function exists( $event_id ) {
		if ( isset( self::$_events[ $event_id ] ) ) {
			return true;
		}
	}

	public function get_event( $event_id ) {
		if ( isset ( self::$_events[ $event_id ] ) ) {
			return self::$_events[ $event_id ];
		}
	}

	public function get_all() {
		return self::$_events;
	}

}