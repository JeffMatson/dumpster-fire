<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Field_Deleted extends Trigger {

	public $id = 'gf-field-deleted';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Field Deleted';
	}

	public function placeholder() {
		return 'Field ID {field_id} has been deleted from form ID {form_id}';
	}

	public function merge_tags() {
		return array(
			'form_id',
			'field_id'
		);
	}

	public function listeners() {
		add_action( 'gform_after_delete_field', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $entry_id, $form_id ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $form_id ) {
				$replacements['{form_id}'] = $form_id;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $entry_id ) {
				$replacements['{entry_id}'] = $entry_id;

				return $replacements;
			}, 10, 1 );
	}

	public function triggered( $entry_id, $form_id ) {

		$this->merge_tag_filters( $entry_id, $form_id );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Field_Deleted() );