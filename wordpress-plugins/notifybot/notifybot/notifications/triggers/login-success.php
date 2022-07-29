<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Login_Success extends Trigger {

	public $id          = 'login-success';
	public $depends_on  = 'login';
	public $placeholder = 'User {user_login} successfully logged in';
	public $label       = 'Successful Logins';
	public $merge_tags  = array(
		'user_login',
		'user_id',
		'user_nicename',
		'user_email',
		'display_name',
	);

	public function local_settings() {
		return array(
			'user' => array(
				'required'    => false,
				'label'       => 'User',
				'sublabel'    => 'If you want this to only monitor a specific user, select it here.',
				'input_type'  => 'select',
				'multiple'    => true,
				'placeholder' => 'Select User',
				'selections'  => $this->get_optional_users()
			)
		);
	}

	public function listeners() {
		add_action( 'wp_login', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $user ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $user ) {
				$replacements['{user_login}'] = $user->user_login;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $user ) {
				$replacements['{user_id}'] = $user->ID;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $user ) {
				$replacements['{user_nicename}'] = $user->user_nicename;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $user ) {
				$replacements['{user_email}'] = $user->user_email;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $user ) {
				$replacements['{display_name}'] = $user->display_name;
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $user ) {

		$user = get_user_by('login', $user);

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $user ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->user ) ) {
					if ( ! in_array( $user->user_login, $optional->user ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters($user);
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Login_Success() );