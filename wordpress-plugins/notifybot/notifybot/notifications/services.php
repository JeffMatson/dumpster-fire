<?php

namespace NotifyBot\Notifications;

class Services {

	private static $_services = array();
	protected static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {
		foreach ( glob( plugin_dir_path( __FILE__ ) . 'services/*' ) as $filename ) {
			require_once( $filename );
		}
	}

	public static function register( $service ) {
		self::$_services[ $service->id ] = $service;
	}

	public function exists( $service_id ) {
		if ( isset( self::$_services[ $service_id ] ) ) {
			return true;
		}
		return false;
	}

	public function get_service( $service_id ) {
		if ( isset ( self::$_services[ $service_id ] ) ) {
			return self::$_services[ $service_id ];
		}
		return false;
	}

	public function get_all() {
		return self::$_services;
	}

	public function get_depends( $depends ) {

		$service_array = array();

		foreach ( self::$_services as $service ) {
			if ( $depends == $service->depends_on ) {
				$service_array[] = $service;
			}
		}

		return $service_array;
	}

	public function send( $service_id, $queue_id, $nb_id, $content ) {
		if ( ! array_key_exists( $service_id, self::$_services ) ) {
			return;
		}
		$service = self::$_services[ $service_id] ;
		if ( ! empty( $service ) && method_exists( $service, 'send' ) ) {
			$service->send( $queue_id, $nb_id, $content );
		}
	}

}

