<?php

namespace NotifyBot;
/**
 * Class Model
 *
 * Methods and properties required across all other models.
 * Extended by Notifications, Queue, Feeds, and Global_Settings models
 *
 * @package NotifyBot
 */
class Model {

	/**
	 * Contains the version of the wp_nb_notify table
	 * @access public
	 * @var float $notify_table_version Table version
	 */
	public $notify_table_version = 0.1;
	/**
	 * Contains the version of the wp_nb_queue table
	 * @access public
	 * @var float $queue_table_version Table version
	 */
	public $queue_table_version = 0.1;
	/**
	 * Contains the version of the wp_nb_global table
	 * @access public
	 * @var float $global_table_version Table version
	 */
	public $global_table_version = 0.1;
	/**
	 * Contains the version of the wp_nb_feeds table
	 * @access public
	 * @var float $feeds_table_version Table version
	 */
	public $feeds_table_version = 0.1;

	/**
	 * An instance of this object
	 *
	 * @access private
	 * @static
	 * @var object $instance The object
	 */
	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return static::$instance;
	}

	/**
	 * Requires the upgrade file
	 * Required when creating tables
	 * @access public
	 */
	public function require_wp_upgrade() {
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	}

	/**
	 * Generates the table name, using the prefix.
	 *
	 * @access public
	 *
	 * @param string $table
	 *
	 * @return string The full table name
	 */
	public function table_name( $table ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'nb_' . $table;
		return $table_name;
	}

	/**
	 * Checks if a particular table exists, based on site option
	 *
	 * @access public
	 *
	 * @param string $table The table to check
	 *
	 * @return bool Returns false if option isn't there or doesn't match the current version
	 */
	public function table_exists( $table ) {
		$version = $table . '_table_version';
		if ( get_site_option( 'nb_' . $table . '_table_version' ) != $this->$version ) {
			return false;
		} else {
			return true;
		}
	}

}