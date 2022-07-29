<?php

namespace NB_EDD\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Payment_Status_Changed extends Trigger {

	public $id = 'edd-payment-status-changed';

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
		return 'Payment Status Changed';
	}

	public function placeholder() {
		return 'Payment status on {payment_id} changed from {old_status} to {new_status}';
	}

	public function merge_tags() {
		return array(
			'payment_id',
			'old_status',
			'new_status'
		);
	}

	public function listeners() {
		add_action( 'edd_update_payment_status', array( $this, 'triggered' ), 10, 3 );
	}

	public function merge_tag_filters( $payment_id, $new_status, $old_status ) {

		$payment_meta = edd_get_payment_meta( $payment_id );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $payment_id ) {
				$replacements['{payment_id}'] = $payment_id;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $old_status ) {
				$replacements['{old_status}'] = $old_status;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $new_status ) {
				$replacements['{new_status}'] = $new_status;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $payment_id, $new_status, $old_status ) {

		$this->merge_tag_filters( $payment_id, $new_status, $old_status );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Payment_Status_Changed() );