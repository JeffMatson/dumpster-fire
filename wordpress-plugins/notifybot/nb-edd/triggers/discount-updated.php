<?php

namespace NB_EDD\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Discount_Updated extends Trigger {

	public $id = 'edd-discount-updated';

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
		return 'Discount Updated';
	}

	public function placeholder() {
		return 'Discount code {discount_code} updated by {user}';
	}

	public function merge_tags() {
		return array(
			'discount_code',
		);
	}

	public function listeners() {
		add_action( 'edd_post_update_discount', array( $this, 'triggered' ), 10, 3 );
	}

	public function merge_tag_filters( $discount_details ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $discount_details ) {
				$replacements['{discount_code}'] = $discount_details['code'];

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $discount_details ) {

		$this->merge_tag_filters( $discount_details );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Discount_Updated() );