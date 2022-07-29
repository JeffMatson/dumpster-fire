<?php

namespace NB_EDD\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Customer_Created extends Trigger {

	public $id = 'edd-customer-created';

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
		return 'Customer Created';
	}

	public function placeholder() {
		return 'Customer {customer_name} was created';
	}

	public function merge_tags() {
		return array(
			'customer_name',
		);
	}

	public function listeners() {
		add_action( 'edd_customer_post_create', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $args ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $args ) {
				$replacements['{customer_name}'] = $args['name'];

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $created, $args ) {

		$this->merge_tag_filters( $args );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Customer_Created() );