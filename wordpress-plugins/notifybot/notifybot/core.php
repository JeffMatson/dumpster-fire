<?php

namespace NotifyBot;
use NotifyBot\Includes\Updater;
use NotifyBot\Models\Global_Settings;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Services;
use NotifyBot\Views;

class Core {

	/**
	 * Used to return an instance of this class
	 * @see get_instance()
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Creates an instance of this class if not instantiated.
	 * If instantiated, returns it.
	 *
	 * @return object $instance The NotifyBot\Core object
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Returns the path of the main NotifyBot directory
	 *
	 * @return string The path
	 */
	public function path() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Returns the URL path to the mainn NotifyBot directory
	 *
	 * @return string The URL
	 */
	public static function url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Returns the location of the NotifyBot API.
	 *
	 * @return string The URL
	 *
	 * @todo This could probably be moved to a class property.
	 */
	public function api_url() {
		return 'https://api.notifybot.io/';
	}

	/**
	 * Runs after NotifyBot is loaded
	 *
	 * Used for add-ons to ensure NotifyBot is loaded already
	 */
	public static function loaded() {
		/**
		 * Fires when NotifyBot is loaded
		 */
		do_action( 'nb_loaded' );
	}

	/**
	 * Adds items to the admin menu.
	 * @see NotifyBot\Views
	 */
	public static function admin_menu() {

		//Loads the List All page
		add_menu_page(
			'NotifyBot',
			'NotifyBot',
			'manage_options',
			'notifybot',
			array( new Views\List_All, 'display' ),
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMzQuNSAyMzQuNSI+PHN0eWxlIHR5cGU9InRleHQvY3NzIj4gIA0KCS5zdDB7ZmlsbDojMDEwMTAxO30NCjwvc3R5bGU+PHBhdGggY2xhc3M9InN0MCIgZD0iTTExNy4zIDQuOEM1NS4zIDQuOCA0LjggNTUuMyA0LjggMTE3LjNzNTAuNCAxMTIuNCAxMTIuNCAxMTIuNCAxMTIuNC01MC40IDExMi40LTExMi40UzE3OS4zIDQuOCAxMTcuMyA0Ljh6TTExNy4zIDIxOS4yYy01Ni4yIDAtMTAxLjktNDUuNy0xMDEuOS0xMDEuOVM2MS4xIDE1LjQgMTE3LjMgMTUuNHMxMDEuOSA0NS43IDEwMS45IDEwMS45UzE3My40IDIxOS4yIDExNy4zIDIxOS4yeiIvPjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xOTAuNSAxMzIuMWMtNi42LTIyLjItMzQuNC0zOS4yLTY4LjQtNDAuOGwtMS44LTExLjVoLTYuMWwtMS44IDExLjRjLTM0LjUgMS4zLTYyLjcgMTguNS02OS40IDQwLjkgLTUuNSAwLjctOS44IDYuMS05LjggMTIuNiAwIDcgNC45IDEyLjcgMTAuOSAxMi43IDAuMiAwIDAuNSAwIDAuNyAwIDkuNSAyMC45IDM4LjEgMzYuMSA3MS45IDM2LjEgMzMuOCAwIDYyLjUtMTUuMiA3MS45LTM2LjIgMC41IDAuMSAxIDAuMSAxLjUgMC4xIDYgMCAxMC45LTUuNyAxMC45LTEyLjdDMjAxLjIgMTM3LjkgMTk2LjQgMTMyLjMgMTkwLjUgMTMyLjF6TTg3IDE2MS4xYy05IDAtMTYuMi03LjMtMTYuMi0xNi4yIDAtOSA3LjMtMTYuMiAxNi4yLTE2LjIgOSAwIDE2LjIgNy4zIDE2LjIgMTYuMkMxMDMuMyAxNTMuOCA5NiAxNjEuMSA4NyAxNjEuMXpNMTQ3LjUgMTYxLjFjLTkgMC0xNi4yLTcuMy0xNi4yLTE2LjIgMC05IDcuMy0xNi4yIDE2LjItMTYuMiA5IDAgMTYuMiA3LjMgMTYuMiAxNi4yQzE2My44IDE1My44IDE1Ni41IDE2MS4xIDE0Ny41IDE2MS4xeiIvPjxjaXJjbGUgY2xhc3M9InN0MCIgY3g9IjExNy4zIiBjeT0iNjkuOCIgcj0iNC42Ii8+PHBhdGggY2xhc3M9InN0MCIgZD0iTTEzMS43IDU5LjlMMTMxLjcgNTkuOWwtMS4zLTEuMyAwIDBjLTcuMy03LjEtMTkuMS03LjEtMjYuMyAwLjIgMCAwIDAgMCAwIDAuMWwwIDAgLTEuMyAxLjMgMCAwYy0wLjYgMC43LTAuNSAxLjcgMC4xIDIuNGwwIDAgMi4zIDIuMyAwIDBjMC43IDAuNSAxLjYgMC41IDIuMiAwbDAgMCAxLjMtMS4zIDAgMGMwIDAgMCAwIDAuMSAwIDQuNy00LjcgMTIuMy00LjggMTctMC4ybDAgMCAxLjMgMS4zIDAgMGMwLjcgMC42IDEuNyAwLjUgMi40LTAuMWwwIDAgMi4zLTIuMyAwIDBDMTMyLjIgNjEuNCAxMzIuMiA2MC41IDEzMS43IDU5Ljl6Ii8+PHBhdGggY2xhc3M9InN0MCIgZD0iTTk0LjIgNTMuOEw5NC4yIDUzLjhsMi4zIDIuMyAwIDBjMC43IDAuNSAxLjYgMC41IDIuMiAwbDAgMCAxLjMtMS4zIDAgMGMwIDAgMCAwIDAuMSAwIDkuNS05LjUgMjQuOS05LjYgMzQuNS0wLjJsMCAwIDEuMyAxLjMgMCAwYzAuNyAwLjYgMS43IDAuNSAyLjQtMC4xbDAgMCAyLjMtMi4zIDAgMGMwLjUtMC43IDAuNS0xLjYgMC0yLjJsMCAwIC0xLjMtMS4zIDAgMCAwIDBDMTI3IDM3LjkgMTA3LjQgMzggOTUuNCA1MC4xYzAgMCAwIDAgMCAwLjFsMCAwIC0xLjMgMS4zIDAgMEM5My42IDUyLjEgOTMuNiA1My4xIDk0LjIgNTMuOHoiLz48cGF0aCBjbGFzcz0ic3QwIiBkPSJNODcgMTMxLjFjLTcuNiAwLTEzLjkgNi4yLTEzLjkgMTMuOSAwIDAuMiAwIDAuMyAwIDAuNSA0LjMtMS40IDkuMS0yLjIgMTQuMS0yLjIgNC44IDAgOS40IDAuNyAxMy42IDIgMC0wLjEgMC0wLjIgMC0wLjNDMTAwLjkgMTM3LjMgOTQuNyAxMzEuMSA4NyAxMzEuMXoiLz48cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTQ4IDEzMS4xYy03LjYgMC0xMy45IDYuMi0xMy45IDEzLjkgMCAwLjIgMCAwLjMgMCAwLjUgNC4zLTEuNCA5LjEtMi4yIDE0LjEtMi4yIDQuOCAwIDkuNCAwLjcgMTMuNiAyIDAtMC4xIDAtMC4yIDAtMC4zQzE2MS45IDEzNy4zIDE1NS43IDEzMS4xIDE0OCAxMzEuMXoiLz48L3N2Zz4=',
			'81'
		);

		// Loads the Add New page
		add_submenu_page(
			'notifybot',
			'Add New',
			'Add New',
			'manage_options',
			'notifybot-add-new',
			array( new Views\Add_New, 'display' )
		);

		// Loads the Settings page
		add_submenu_page(
			'notifybot',
			'Settings',
			'Settings',
			'manage_options',
			'notifybot-settings',
			array( new Views\Settings, 'display' )
		);
	}

	/**
	 * Actions to run on activation
	 *
	 * Mainly table creation, and queue cron scheduling.
	 */
	public static function run_activation() {
		/**
		 * Fires before core activation hooks are run
		 */
		do_action( 'nb_pre_activation' );

		// Instantiates the required models
		$notifications_model   = Notifications::get_instance();
		$queue_model           = Queue::get_instance();
		$global_settings_model = Global_Settings::get_instance();

		// If wp_nb_notify doesn't exist, create it
		if ( ! $notifications_model->table_exists( 'notify' ) ) {
			$notifications_model->create_table();
		}

		// If wp_nb_queue doesn't exist, create it
		if ( ! $queue_model->table_exists( 'queue' ) ) {
			$queue_model->create_table();
		}

		// If wp_nb_global doesn't exist, create it
		if ( ! $global_settings_model->table_exists( 'global' ) ) {
			$global_settings_model->create_table();
		}

		// Schedule queue processing cron
		if ( wp_get_schedule('nb_process_queue') == false ) {
			wp_schedule_event( time(), 'nb_queue', 'nb_process_queue' );
		}

		/**
		 * Fires after NotifyBot activation actions are run.
		 */
		do_action( 'nb_activation' );
	}

	/**
	 * Runs actions required in deactivation
	 *
	 * Mainly just clearing the cron task
	 */
	public function run_deactivation() {
		/**
		 * Fires before core deactivation actions run
		 */
		do_action( 'nb_pre_deactivation' );

		// Clears the queue cron task
		wp_clear_scheduled_hook( 'nb_process_queue' );

		/**
		 * Fires after core deactivation have run
		 */
		do_action( 'nb_deactivation' );
	}

	/**
	 * Finds all merge tags within the message content
	 * Creates and array of merge tags and replacement content
	 *
	 * @param string $message The message that will be sent in the notification
	 *
	 * @return array $replacements Array containing the merge tag found, and replacement content
	 */
	public function process_merge_tags( $message ) {

		new Global_Merge_Tags();

		preg_match_all( '/\{([^\}].*?)\}/', $message, $matches );

		$replacements = array_flip($matches['0']);

		foreach ( $replacements as $key => $value ) {
			$without_brackets = str_replace(array('{', '}'), '', $key);
			if ( $this->found_merge_tags( $without_brackets ) != true ) {
				unset($replacements[$key]);
			}
		}

		/**
		 * Filters the merge tags to be replaced
		 *
		 * @param array $replacements The merge tag keys to be replaced
		 */
		$replacements = apply_filters( 'nb_process_merge_tag_replacements', $replacements );

		return $replacements;
	}

	/**
	 * Attempts to find a merge tag within the string
	 *
	 * @param string $tag The merge tag to look for
	 *
	 * @return bool True if found merge tags
	 */
	public function found_merge_tags( $tag ) {
		if ( in_array( $tag, $this->merge_tags() ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Whitelists allowed merge tags.
	 *
	 * @return array $merge_tags Contains all allowed merge tags
	 */
	public function merge_tags() {
		$merge_tags = array(
			'site_name',
			'site_url',
			'user'
		);
		/**
		 * Filters the allowed merge tags
		 * Used to add merge tags that are not global
		 *
		 * @param array $merge_tags Whitelisted merge tags
		 */
		$merge_tags = apply_filters( 'nb_merge_tags', $merge_tags );

		return $merge_tags;
	}

	/**
	 * Builds the message content to send
	 *
	 * @param string $message The raw message content, before merge tags are processed
	 *
	 * @return string $message The message content, with merge tags replaced
	 */
	public function build_message_content( $message ) {
		$tag_data = $this->process_merge_tags( $message );
		return strtr( $message, $tag_data );
	}

	/**
	 * Sends the notifications
	 *
	 * @param array $to_send Contains the notifications that need to be sent
	 */
	public function send_notification( $to_send ) {
		foreach ( $to_send as $sending ) {
			$details = Notifications::get_instance()->get_notification_by_id( $sending->nb_id );

			Services::get_instance()->send( $details->service, $sending->id, $sending->nb_id, $sending->message );

			Queue::get_instance()->delete_item( $sending->id );

			/**
			 * Fires when a queue item is sent.
			 *
			 * Used to monitor when a specific notification is sent
			 *
			 * @return int $sending->id The ID of the notification being sent
			 * @return object $sending Contains the details of the notification being sent
			 */
			do_action( 'sent_nb_item', $sending->id, $sending );
		}
		/**
		 * Fires when sending has completed for all items
		 *
		 * @return array $to_send Contains all of the notification objects that were sent
		 */
		do_action( 'nb_send_complete', $to_send );
	}

	/**
	 * Generates a pseudo-random string to use for obscurity and IDs
	 * Not cryptographically secure by any means.  Don't be dumb.
	 *
	 * @param int $length The length of the string to generate.  Defaults to 10
	 *
	 * @return string $random_string The randomly generated string
	 */
	public function generate_string( $length = 10 ) {
		$string = wp_generate_password( $length, false );
		return $string;
	}

	public function updater() {

		$license_key = trim( get_option( 'nb_license_key' ) );
		$notifybot_file = $this->path() . 'notifybot.php';

		$nb_updater = new Updater( NB_API_URL, $notifybot_file, array(
				'version'   => get_plugin_data( $notifybot_file )['Version'],
				'license'   => $license_key,
				'item_name' => NB_CORE,
				'author'    => 'NotifyBot'
			)
		);

	}

	public function activate_license() {

		if( isset( $_POST['nb_license_activate'] ) ) {

			if( ! check_admin_referer( 'nb_activate_nonce', 'nb_activate_nonce' ) )
				return;


			if ( isset( $_POST['nb_license_key'] ) )
			update_option( 'nb_license_key', sanitize_key( $_POST['nb_license_key'] ) );

			$license = trim( get_option( 'nb_license_key' ) );

			$api_params = array(
				'edd_action'=> 'activate_license',
				'license' 	=> $license,
				'item_name' => urlencode( NB_CORE ),
				'url'       => home_url()
			);

			$response = wp_remote_post( NB_API_URL, array( 'timeout' => 15, 'body' => $api_params ) );

			if ( is_wp_error( $response ) )
				return false;

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			update_option( 'nb_license_status', $license_data->license );

		}
	}

	public function deactivate_license() {

		if( isset( $_POST['nb_license_deactivate'] ) ) {

			if( ! check_admin_referer( 'nb_activate_nonce', 'nb_activate_nonce' ) )
				return;

			$license = trim( get_option( 'nb_license_key' ) );

			$api_params = array(
				'edd_action'=> 'deactivate_license',
				'license' 	=> $license,
				'item_name' => urlencode( NB_CORE ),
				'url'       => home_url()
			);

			$response = wp_remote_post( NB_API_URL, array( 'timeout' => 15, 'body' => $api_params ) );

			if ( is_wp_error( $response ) )
				return false;

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if( $license_data->license == 'deactivated' )
				delete_option( 'nb_license_status' );

		}
	}

	public function check_license() {

		global $wp_version;

		$license = trim( get_option( 'edd_sample_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $license,
			'item_name' => urlencode( NB_CORE ),
			'url'       => home_url()
		);

		$response = wp_remote_post( NB_API_URL, array( 'timeout' => 15, 'body' => $api_params ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( $license_data->license == 'valid' ) {
			update_option( 'nb_license_status', $license_data->license );
		} else {
			delete_option( 'nb_license_status' );
		}
	}

}