<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Entry_Viewed extends Trigger {

	public $id = 'gf-entry-viewed';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Entry Viewed';
	}

	public function placeholder() {
		return 'Entry ID {entry_id} has been viewed on {form_title}';
	}

	public function merge_tags() {
		return array(
			'form_id',
			'form_title',
			'form_description',
			'entry_id'
		);
	}

	public function listeners() {
		add_action( 'gform_entry_detail_content_after', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $entry, $form ) {

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
			function ( $replacements ) use ( $entry ) {
				$replacements['{entry_id}'] = $entry['id'];

				return $replacements;
			}, 10, 1 );
	}

	public function triggered( $entry, $form ) {

		$this->merge_tag_filters( $entry, $form );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Entry_Viewed() );