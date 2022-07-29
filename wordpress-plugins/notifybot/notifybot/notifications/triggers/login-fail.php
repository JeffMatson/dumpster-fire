<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Login_Fail extends Trigger {

	public $id          = 'login-fail';
	public $depends_on  = 'login';
	public $placeholder = 'Login failed for user: {failed_user}';
	public $label       = 'Failed Logins';
	public $merge_tags  = array(
		'failed_user',
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
		add_action( 'wp_login_failed', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $username ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $username ) {
				$replacements['{failed_user}'] = $username;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $username ) {

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $username ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->user ) ) {
					if ( ! in_array( $username, $optional->user ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters( $username );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Login_Fail() );