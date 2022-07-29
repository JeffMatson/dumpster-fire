<?php

namespace NotifyBot\Notifications\Services;
use NotifyBot\Core;
use NotifyBot\Models\Feeds;
use NotifyBot\Notifications;

class RSS_Local extends Service {

	public $id = 'rss-local';
	public $label = 'Local RSS';
	public $depends_on = 'rss';
	public static $feed = null;

	public function local_settings() {
		return array(
			'slug' => array(
				'required'      => true,
				'label'         => 'Feed Slug',
				'sublabel'      => '',
				'input_type'    => 'text',
				'placeholder'   => 'mycustomrsspath',
				'default_value' => Core::get_instance()->generate_string(),
				'global_key'   => 'rss_slug_'
			)
		);
	}

	public function add_feeds() {
		if ( Feeds::get_instance()->table_exists( 'feeds' ) ) {
			$feed_slugs = Feeds::get_instance()->get_all_feed_slugs();

			foreach ( $feed_slugs as $slug ) {
				RSS_Local::$feed = $slug->feed_slug;
				add_feed( $slug->feed_slug, array( $this, 'create_feed' ) );
			}

			$count_feed_urls = count( $feed_slugs );
			if ( get_option( 'nb_feed_count' ) !== $count_feed_urls ) {
				global $wp_rewrite;
				$wp_rewrite->flush_rules( false );
			}
		}
	}

	public static function send( $queue_id, $nb_id, $content ) {

		$details = \NotifyBot\Models\Notifications::get_instance()->get_notification_by_id( $nb_id );
		$options = json_decode( $details->options );

		$slug = $options->service->required->slug;

		$item_title = $content;
		$item_content = $content;

		if ( ! Feeds::get_instance()->table_exists( 'feeds' ) ) {
			Feeds::get_instance()->create_table();
		}

		Feeds::get_instance()->insert_item( $slug, $item_title, $item_content );
	}

	public function create_feed() {

		$feed_slugs = Feeds::get_instance()->get_all_feed_slugs();
		$feed_url = get_query_var( 'feed' );

		foreach ( $feed_slugs as $slug ) {
			if ( $slug->feed_slug == $feed_url ) {
				$feed_slug = $slug->feed_slug;
				break;
			}
		}

		if ( ! isset( $feed_slug ) ) {
			return;
		}

		$feed = new \FeedWriter\RSS2();

		$site_url = get_site_url();
		$feed->setTitle( $feed_slug );
		$feed->setLink( $site_url );
		$feed->setDescription( $feed_slug );
		$feed->setDate( Feeds::get_instance()->last_updated( $feed_slug ) );

		foreach ( Feeds::get_instance()->get_all_by_slug( $feed_slug ) as $feed_item ) {
			$item = $feed->createNewItem();
			$item->setTitle( $feed_item->item_title . ' on ' . $feed_item->item_datetime );
			$item->setLink( $site_url . 'nb_rss_id_' . $feed_item->id );
			$item->setId( $site_url . 'nb_rss_id_' . $feed_item->id, true );
			$item->setDescription( $feed_item->item_content );

			$item->setDate( $feed_item->item_datetime );

			$feed->addItem( $item );
		}

		$feed_data = $feed->generateFeed();
		header( 'Content-Type: application/rss+xml' );
		echo $feed_data;
	}

}

$feeds = new RSS_Local();
Notifications\Services::register( $feeds );
add_action( 'init', array( $feeds, 'add_feeds' ) );



