<?php

namespace NotifyBot\Notifications\Services;

use NotifyBot\Models\Global_Settings;
use NotifyBot\Models\Notifications;
use NotifyBot\Notifications\Services;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Email_Remote extends Service {

	public $id         = 'email-remote';
	public $label      = 'Remote Email';
	public $depends_on = 'email';

	public function global_settings() {
		return array(
			'id'       => $this->id,
			'header'   => 'Remote Email Settings',
			'required' => array(
				'smtp_host',
				'smtp_port',
				'smtp_user',
				'smtp_password',
				'smtp_from_address',
			),
			'sections' => array(
				'smtp-settings' => array(
					'label'   => 'SMTP Settings',
					'options' => array(
						'smtp_host'     => array(
							'label'       => 'SMTP Host',
							'sublabel'    => 'Enter your SMTP host.',
							'input_type'  => 'text',
							'placeholder' => 'localhost',
						),
						'smtp_port'     => array(
							'label'       => 'SMTP Port',
							'sublabel'    => 'Enter your SMTP post.',
							'input_type'  => 'text',
							'placeholder' => '587',
						),
						'smtp_user'     => array(
							'label'       => 'SMTP User',
							'sublabel'    => 'Enter your SMTP username.',
							'input_type'  => 'text',
							'placeholder' => 'user@example.com',
						),
						'smtp_password' => array(
							'label'       => 'SMTP Password',
							'sublabel'    => 'Enter your SMTP password.',
							'input_type'  => 'password',
							'placeholder' => '',
						),
						'smtp_from_address' => array(
							'label'       => 'From Address',
							'sublabel'    => 'Enter the address that mail will be sent from',
							'input_type'  => 'text',
							'placeholder' => 'user@example.com',
						),

					)
				)
			)
		);
	}
	
	public function local_settings() {
		return array(
			'to' => array(
				'required'    => true,
				'label'       => 'Send Email To',
				'sublabel'    => 'Enter the email address that you want to send the email notification to',
				'input_type'  => 'select',
				'multiple'    => true,
				'placeholder' => 'user@example.com',
				'selections'  => $this->get_users(),
			)
		);
	}

	public static function send( $queue_id, $nb_id, $content ) {

		$details = Notifications::get_instance()->get_notification_by_id( $nb_id );
		$options = json_decode( $details->options );

		$to_addresses = $options->service->required->to;

		$subject = 'Notification for: ' . Triggers::get_instance()->get_trigger( $details->event_trigger )->label();

		require_once( ABSPATH . 'wp-includes/pluggable.php' );
		add_action( 'phpmailer_init', array( get_called_class(), 'run_smtp_auth' ) );

		if ( is_array( $to_addresses ) ) {
			foreach( $to_addresses as $to_address ) {
				if ( ! filter_var( $to_address, FILTER_VALIDATE_EMAIL ) ) {
					$user = get_user_by( 'login', $to_address );
					if ( $user !== false ) {
						$to_address = $user->user_email;
					}
				}
				wp_mail( $to_address, $subject, $content );
			}
		}

		do_action('nb_sent', $queue_id, $nb_id);
	}

	public static function run_smtp_auth( \PHPMailer $phpmailer ) {
		$global_settings_model = Global_Settings::get_instance();

		$phpmailer->isSMTP();

		$phpmailer->Host = $global_settings_model->get_value( 'smtp_host' );
		$phpmailer->Port = $global_settings_model->get_value( 'smtp_port' );
		$phpmailer->Username = $global_settings_model->get_value( 'smtp_user' );
		$phpmailer->Password = $global_settings_model->get_value( 'smtp_password' );
		$phpmailer->From = $global_settings_model->get_value( 'smtp_from_address' );
		$phpmailer->SMTPAuth = true;
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

Services::register( new Email_Remote() );


