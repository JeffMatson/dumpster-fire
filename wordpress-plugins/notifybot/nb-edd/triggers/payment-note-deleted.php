<?php

namespace NB_EDD\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Payment_Note_Deleted extends Trigger {

	public $id = 'edd-payment-edited';

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
		return 'Payment Note Deleted';
	}

	public function placeholder() {
		return 'Note was deleted from payment ID {payment_id} by {user}';
	}

	public function merge_tags() {
		return array(
			'payment_id'
		);
	}

	public function listeners() {
		add_action( 'edd_post_delete_payment_note', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $note_id, $payment_id ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $payment_id ) {
				$replacements['{payment_id}'] = $payment_id;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $note_id, $payment_id ) {

		$this->merge_tag_filters( $note_id, $payment_id );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Payment_Note_Deleted() );