<?php

namespace NotifyBot\Notifications\Services;
use NotifyBot\Core;
use NotifyBot\Models\Feeds;
use NotifyBot\Notifications;

class Webhook_Custom extends Service {

	public $id = 'webhook-custom';
	public $label = 'Custom Endpoint';
	public $depends_on = 'webhook';

	public function local_settings() {
		return array(
			'endpoint' => array(
				'required'      => true,
				'label'         => 'Endpoint URL',
				'sublabel'      => '',
				'input_type'    => 'text',
				'placeholder'   => 'https://example.com/api/notifybot',
				'global_key'    => 'webhook_endpoint_'
			),
			'parameters' => array(
				'required'    => false,
				'label'       => 'Group Parameters (All Events)',
				'sublabel'    => '',
				'input_type'  => 'list',
				'columns'     => 2,
				'placeholder' => array(
					'Key',
					'Value',
				)
			),
		);
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

}

Notifications\Services::register( new Webhook_Custom() );
