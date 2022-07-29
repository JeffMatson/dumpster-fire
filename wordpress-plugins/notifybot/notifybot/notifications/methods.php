<?php

namespace NotifyBot\Notifications;

class Methods {

	private static $_methods = array();
	protected static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {
		foreach ( glob( plugin_dir_path( __FILE__ ) . 'methods/*.php' ) as $filename ) {
			require_once( $filename );
		}
	}

	public static function register( $method ) {
		self::$_methods[ $method->id ] = $method;
	}

	public function exists( $method_id ) {
		if ( isset( self::$_methods[ $method_id ] ) ) {
			return true;
		}
		return false;
	}

	public function get_method( $method_id ) {
		if ( isset ( self::$_methods[ $method_id ] ) ) {
			return self::$_methods[ $method_id ];
		}
		return false;
	}

	public function get_all() {
		return self::$_methods;
	}

}


