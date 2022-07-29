<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Entry_Created extends Trigger {

	public $id = 'gf-entry-created';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Entry Created';
	}

	public function placeholder() {
		return 'Entry ID {entry_id} has been created on {form_title}';
	}

	public function merge_tags() {
		return array(
			'form_id',
			'form_title',
			'form_description',
			'submitted_ip',
			'user_agent',
			'form_url'
		);
	}

	public function listeners() {
		add_action( 'gform_entry_created', array( $this, 'triggered' ), 10, 2 );
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
				$replacements['{submitted_ip}'] = $entry['ip'];

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $entry ) {
				$replacements['{user_agent}'] = $entry['user_agent'];

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $entry ) {
				$replacements['{form_url}'] = $entry['source_url'];

				return $replacements;
			}, 10, 1 );
	}

	public function triggered( $entry, $form ) {

		$this->merge_tag_filters( $entry, $form );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Entry_Created() );