<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Entry_Deleted extends Trigger {

	public $id = 'gf-entry-deleted';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Note Deleted';
	}

	public function placeholder() {
		return 'Entry ID {entry_id} was deleted';
	}

	public function merge_tags() {
		return array(
			'entry_id'
		);
	}

	public function listeners() {
		add_action( 'gform_delete_lead', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $entry_id ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $entry_id ) {
				$replacements['{entry_id}'] = $entry_id;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $entry_id ) {

		$this->merge_tag_filters( $entry_id );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Entry_Deleted() );