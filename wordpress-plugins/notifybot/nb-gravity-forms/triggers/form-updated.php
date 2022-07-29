<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Form_Updated extends Trigger {

	public $id = 'gf-form-updated';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Form Updated';
	}

	public function placeholder() {
		return 'Form {form_title} updated by: {user}';
	}

	public function merge_tags() {
		return array(
			'form_id',
			'form_title',
			'form_description',
		);
	}

	public function listeners() {
		add_action( 'gform_after_save_form', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $form ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $form ) {
				$replacements['{form_id}'] = $form['id'];

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $form ) {
				$replacements['{form_title}'] = $form['title'];

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $form ) {
				$replacements['{form_description}'] = $form['description'];

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $form, $is_new ) {

		if ( $is_new ) {
			return;
		}

		$this->merge_tag_filters( $form );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Form_Updated() );