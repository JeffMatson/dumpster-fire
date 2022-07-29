<?php

namespace NotifyBot\Models;
use NotifyBot\Model;
use NotifyBot\Core;

class Queue extends Model {

	protected static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Defines the number of queue items to be processed at once.
	 *
	 * @access public
	 * @var int $rate_limit
	 */
	public $rate_limit = 5;
	/**
	 * Defines how often the cron should fire, in seconds.
	 *
	 * @access public
	 * @var int $cron_interval Number of seconds between cron hits
	 */
	public $cron_interval = 1;

	/**
	 * Creates the wp_nb_queue table and updates version
	 * @access public
	 */
	public function create_table() {
		global $wpdb;

		$this->require_wp_upgrade();
		$collation = $wpdb->get_charset_collate();
		$table = $this->table_name( 'queue' );

		$sql = 'CREATE TABLE ' . $table . " (
			id int(11) NOT NULL AUTO_INCREMENT,
			nb_id int(11) NOT NULL,
			message mediumtext NOT NULL,
			PRIMARY KEY  (id)
		) $collation;";
		dbDelta( $sql );

		update_site_option( 'nb_queue_table_version', $this->queue_table_version );
	}

	/**
	 * Defines our custom cron timing
	 *
	 * @param array $timings Contains existing cron timings
	 * @return array $timings Cron timings after the custom timing is added.
	 */
	public function add_cron_timing( $timings ) {
		$timings['nb_queue'] = array(
			'interval' => $this->cron_interval,
			'display' => __( 'Once every 10 seconds', 'notifybot' )
		);
		return $timings;
	}

	/**
	 * Adds an array of items to the queue.
	 *
	 * @access public
	 * @param array $items Contains the items to be queued
	 */
	public function add_to_queue( $items ) {

		$prevent_queuing = false;
		$prevent_queuing = apply_filters( 'nb_prevent_queue', $prevent_queuing, $items );

		if ( $prevent_queuing == true ) {
			return;
		}

		if ( is_array( $items ) ) {

			/**
			 * Filters the items before they are queued
			 *
			 * @param array $items Items to be added to the queue
			 */
			$items = apply_filters( 'nb_pre_items_queued', $items );

			$notifications = Notifications::get_instance();

			foreach ( $items as $item ) {
				// Obtains the full notification details
				$details   = $notifications->get_notification_by_id( $item->id );
				$notify_id = $details->id;
				$details->options = json_decode($details->options);

				$allowed = apply_filters( 'nb_queue_allowed_' . $details->event_trigger, true, $details );
				if ( $allowed === false ) {
					return;
				}

				$content = esc_html( Core::get_instance()->build_message_content( $details->message ) );

				/**
				 * Filters the message content before it is queued
				 *
				 * @param string $content The queued message content
				 */
				$content = apply_filters( 'nb_pre_item_queued', $content );

				// Adds the queue item
				$this->insert_item( $notify_id, $content );
			}

			/**
			 * Runs after items are added to the queue
			 *
			 * @param array $items Contains the items that were sent to the queue
			 */
			do_action( 'nb_items_queued', $items );
		}

	}

	/**
	 * Processes the queue items
	 * @access public
	 */
	public static function process_queue() {
		$to_send = self::get_instance()->get_items();
		/**
		 * Filters the queued items to be processed, before they are sent
		 *
		 * @param array $to_send The queued items
		 */
		$to_send = apply_filters( 'nb_pre_processed_queue', $to_send );

		// Sends notifications for the processed queue items
		Core::get_instance()->send_notification($to_send);
		/**
		 * Fires after the queue is processed
		 *
		 * @param array $to_send The queue items that were sent
		 */
		do_action( 'nb_processed_queue', $to_send );
	}

	/**
	 * Gets number of queue items, based on rate limit
	 *
	 * @access public
	 * @return object
	 */
	public function get_items() {
		global $wpdb;
		$table = $this->table_name( 'queue' );
		$rate_limit = $this->rate_limit;

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table LIMIT %d", $rate_limit ) );
		return $results;
	}

	/**
	 * Gets the queue details of a queued item
	 *
	 * @param int $queue_id The ID of the queue item to get
	 *
	 * @return object The queue item details
	 */
	public function get_details( $queue_id ) {
		global $wpdb;
		$table = $this->table_name( 'queue' );

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $queue_id ) );
		return $result;
	}

	/**
	 * Deletes an item from the queue
	 *
	 * @access public
	 * @param int $queue_id The queue ID of the item to delete
	 */
	public function delete_item( $queue_id ) {
		global $wpdb;
		$table = $this->table_name( 'queue' );

		$wpdb->delete( $table, array( 'id' => $queue_id ), array( '%s' ) );
	}

	/**
	 * Inserts an item into the queue
	 *
	 * @param int    $notify_id ID of the notification to be added to the queue
	 * @param string $content   The notification message content
	 */
	public function insert_item( $notify_id, $content ) {
		global $wpdb;
		$table = $this->table_name( 'queue' );

		$wpdb->insert(
			$table,
			array(
				'nb_id' => $notify_id,
				'message' => $content
			),
			array(
				'%d',
				'%s'
			)
		);

		/**
		 * Runs after a queue item is inserted into the database
		 *
		 * @param int $notify_id Notification ID of the item that was inserted
		 * @param string $content Content to be sent in the notification once processed
		 */
		add_action( 'nb_queue_item_inserted', $notify_id, $content );
	}

}