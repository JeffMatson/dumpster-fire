<?php

namespace NotifyBot\Notifications\Services;
use NotifyBot\Notifications\Services;
use NotifyBot\Models\Notifications;
use NotifyBot\Notifications\Triggers;

class Email_Local extends Service {

	public $id         = 'email-local';
	public $label      = 'Local Email';
	public $depends_on = 'email';

	public function local_settings() {
		return array(
			'to' => array(
				'required'    => true,
				'label'       => 'Send Email To',
				'sublabel'    => 'Enter the email address that you want to send the email notification to',
				'input_type'  => 'select',
				'multiple'    => true,
				'placeholder' => 'Select users or enter custom email addresses',
				'selections'  => $this->get_users()
			),
			'from' => array(
				'required'    => false,
				'label'       => 'From Address',
				'sublabel'    => 'If you would like a specific email to be displayed as the "from" address, enter it here.',
				'input_type'  => 'select',
				'multiple'    => false,
				'placeholder' => 'Select a user or enter a custom email address',
			)
		);
	}

	public static function send( $queue_id, $nb_id, $content ) {

		$details = Notifications::get_instance()->get_notification_by_id( $nb_id );
		$options = json_decode( $details->options );

		$to_addresses = $options->service->required->to;

		if ( isset( $options->service->optional->from ) ) {
			$from = $options->service->optional->from;
		} else {
			$from = null;
		}

		$subject = 'Notification for: ' . Triggers::get_instance()->get_trigger( $details->event_trigger )->label();

		require_once( ABSPATH . 'wp-includes/pluggable.php' );

		if ( is_array( $to_addresses ) ) {
			foreach( $to_addresses as $to_address ) {
				if ( ! filter_var( $to_address, FILTER_VALIDATE_EMAIL ) ) {
					$user = get_user_by( 'login', $to_address );
					if ( $user !== false ) {
						$to_address = $user->user_email;
					}
				}
				wp_mail( $to_address, $subject, $content, $from );
			}
		}

		do_action('nb_sent', $queue_id, $nb_id);
	}

	public function get_users() {
		$users     = get_users();
		$user_list = array();
		foreach ( $users as $user ) {
			$user_list[] = $user->user_login;
		}

		return $user_list;
	}

}

Services::register( new Email_Local() );