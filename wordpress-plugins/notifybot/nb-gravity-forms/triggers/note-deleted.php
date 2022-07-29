<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Note_Deleted extends Trigger {

	public $id = 'gf-note-deleted';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Note Deleted';
	}

	public function placeholder() {
		return 'Note {note_id} was deleted from entry {entry_id}';
	}

	public function merge_tags() {
		return array(
			'note_id',
			'entry_id',
		);
	}

	public function listeners() {
		add_action( 'gform_pre_note_deleted', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $note_id, $entry_id ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $note_id ) {
				$replacements['{note_id}'] = $note_id;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $entry_id ) {
				$replacements['{entry_id}'] = $entry_id;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $note_id, $entry_id ) {

		$this->merge_tag_filters( $note_id, $entry_id);

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Note_Deleted() );