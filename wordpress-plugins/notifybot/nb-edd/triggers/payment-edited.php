<?php

namespace NB_EDD\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Payment_Edited extends Trigger {

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
		return 'Payment Edited';
	}

	public function placeholder() {
		return 'Payment ID {payment_id} was edited by {user}';
	}

	public function merge_tags() {
		return array(
			'payment_id'
		);
	}

	public function listeners() {
		add_action( 'edd_update_edited_purchase', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $payment_id ) {

		$payment_meta = edd_get_payment_meta( $payment_id );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $payment_id ) {
				$replacements['{payment_id}'] = $payment_id;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $payment_id ) {

		$this->merge_tag_filters( $payment_id );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Payment_Edited() );