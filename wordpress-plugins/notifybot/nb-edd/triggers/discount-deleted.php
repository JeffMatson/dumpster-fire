<?php

namespace NB_EDD\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Discount_Deleted extends Trigger {

	public $id = 'edd-discount-deleted';

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
		return 'Discount Deleted';
	}

	public function placeholder() {
		return 'Discount ID {discount_id} deleted by {user}';
	}

	public function merge_tags() {
		return array(
			'discount_id',
		);
	}

	public function listeners() {
		add_action( 'edd_post_delete_discount', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $discount_id ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $discount_id ) {
				$replacements['{discount_id}'] = $discount_id;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $discount_id ) {

		$this->merge_tag_filters( $discount_id );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Discount_Deleted() );