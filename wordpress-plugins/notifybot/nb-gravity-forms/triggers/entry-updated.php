<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Entry_Updated extends Trigger {

	public $id = 'gf-entry-updated';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Entry Updated';
	}

	public function placeholder() {
		return 'Entry {entry_id} was updated on form {form_title}';
	}

	public function merge_tags() {
		return array(
			'form_id',
			'form_title',
			'form_description',
			'entry_id',
		);
	}

	public function listeners() {
		add_action( 'gform_after_update_entry', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $form, $entry_id ) {

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

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $entry_id ) {
				$replacements['{entry_id}'] = $entry_id;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $form, $entry_id ) {

		$this->merge_tag_filters( $form, $entry_id );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Entry_Updated() );