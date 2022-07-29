<?php

namespace NB_EDD\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Product_Downloaded extends Trigger {

	public $id = 'edd-product-downloaded';

	public function depends_on() {
		return array( 'edd' );
	}

	public function global_settings_required() {
		return array();
	}

	public function settings_required() {
		return array();
	}

	public function settings_optional() {
		return array();
	}

	public function label() {
		return 'Product Downloaded';
	}

	public function placeholder() {
		return 'Product ID {product_id} has been downloaded by user with email address {email}';
	}

	public function merge_tags() {
		return array(
			'product_id',
			'email',
		);
	}

	public function listeners() {
		add_action( 'edd_process_verified_download', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $product_id, $email ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $product_id ) {
				$replacements['{product_id}'] = $product_id;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $email ) {
				$replacements['{email}'] = $email;

				return $replacements;
			}, 10, 1 );
	}

	public function triggered( $product_id, $email ) {

		$this->merge_tag_filters( $product_id, $email );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Product_Downloaded() );