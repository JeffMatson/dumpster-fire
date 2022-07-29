<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Form_Activated extends Trigger {

	public $id = 'gf-form-activated';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Form Activated';
	}

	public function placeholder() {
		return 'Form activated: {form_id}';
	}

	public function merge_tags() {
		return array(
			'form_id',
		);
	}

	public function listeners() {
		add_action( 'gform_post_form_activated', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $form_id ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $form_id ) {
				$replacements['{form_id}'] = $form_id;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $form_id ) {

		$this->merge_tag_filters( $form_id );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Form_Activated() );