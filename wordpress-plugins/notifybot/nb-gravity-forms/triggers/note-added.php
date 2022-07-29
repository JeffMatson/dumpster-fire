<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Note_Added extends Trigger {

	public $id = 'gf-note-added';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Note Added';
	}

	public function placeholder() {
		return 'A note was added to entry {entry_id} by {user_id}';
	}

	public function merge_tags() {
		return array(
			'note_id',
			'entry_id',
			'user_id',
			'user_name',
			'note_content',
			'note_type'
		);
	}

	public function listeners() {
		add_action( 'gform_post_note_added', array( $this, 'triggered' ), 10, 6 );
	}

	public function merge_tag_filters( $insert_id, $entry_id, $user_id, $user_name, $note, $note_type ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $insert_id ) {
				$replacements['{note_id}'] = $insert_id;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $entry_id ) {
				$replacements['{entry_id}'] = $entry_id;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $user_id ) {
				$replacements['{user_id}'] = $user_id;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $user_name ) {
				$replacements['{user_name}'] = $user_name;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $note ) {
				$replacements['{note_content}'] = $note;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $note_type ) {
				$replacements['{note_type}'] = $note_type;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $insert_id, $entry_id, $user_id, $user_name, $note, $note_type ) {

		$this->merge_tag_filters( $insert_id, $entry_id, $user_id, $user_name, $note, $note_type );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Note_Added() );