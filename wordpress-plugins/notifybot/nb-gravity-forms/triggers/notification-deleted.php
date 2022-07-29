<?php

namespace NB_Gravity_Forms\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Notification_Deleted extends Trigger {

	public $id = 'gf-notification-deleted';

	public function depends_on() {
		return array( 'gravity-forms' );
	}

	public function label() {
		return 'Notification Deleted';
	}

	public function placeholder() {
		return 'Notification ID {notification_id} deleted from form {form_title} by: {user}';
	}

	public function merge_tags() {
		return array(
			'form_id',
			'form_title',
			'form_description',
			'notification_id'
		);
	}

	public function listeners() {
		add_action( 'gform_pre_notification_deleted', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $notification_id, $form ) {

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
			function ( $replacements ) use ( $notification_id ) {
				$replacements['{notification_deleted}'] = $notification_id;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $notification_id, $form ) {

		$this->merge_tag_filters( $notification_id, $form );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Notification_Deleted() );