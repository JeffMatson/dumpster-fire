<?php
/**
 * Handles database connections for the plugin.
 *
 * @since   1.0
 * @package JM_SQL_Query_Mail\SQL_DB
 */

namespace JM_SQL_Query_Mail;

/**
 * Connects to the defined database.
 *
 * @since 1.0
 */
class SQL_DB {

	/**
	 * An instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var wpdb|false Contains an instance of the WPDB class. Defaults to false.
	 */
	public static $_instance = false;

	/**
	 * The database user.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var string The database user.
	 */
	public static $user = 'REPLACEME';

	/**
	 * The database password.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var string The database password.
	 */
	public static $password = 'REPACEME';

	/**
	 * The database to connect to.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var string The database to connect to.
	 */
	public static $database = 'REPLACEME';

	/**
	 * The database host.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var string The database host.
	 */
	public static $host = 'REPLACEME';

	/**
	 * Gets an instance of the wpdb class.
	 *
	 * If an instance already exists, just use that.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses JM_SQL_Query_Mail\SQL_DB::$_instance
	 * @uses JM_SQL_Query_Mail\SQL_DB::$user
	 * @uses JM_SQL_Query_Mail\SQL_DB::$password
	 * @uses JM_SQL_Query_Mail\SQL_DB::$database
	 * @uses JM_SQL_Query_Mail\SQL_DB::$host
	 *
	 * @return wpdb
	 */
	public static function get_instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new \wpdb( self::$user, self::$password, self::$database, self::$host );
		}

		return self::$_instance;
	}

}
