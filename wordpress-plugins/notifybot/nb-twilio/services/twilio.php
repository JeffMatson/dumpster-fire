<?php

namespace NotifyBot\Notifications\Services;
use NotifyBot\Models\Global_Settings;
use NotifyBot\Notifications\Services;
use NotifyBot\Models\Notifications;
use NotifyBot\Notifications\Triggers;

class Twilio extends Service {

	public $id = 'twilio';

	public function label() {
		return 'Twilio';
	}

	public function depends_on() {
		return array( 'sms' );
	}

	public function character_limit() {
		return false;
	}

	public function global_settings_required() {
		return array(
			'id'      => $this->id,
			'header'  => 'Twilio SMS Settings',
			'sections' => array(
				'twilio-api-keys' => array(
					'label'   => 'Twilio API Keys',
					'options' => array(
						'twilio_account_sid'     => array(
							'label'       => 'Account SID',
							'sublabel'    => 'Enter your Twilio Account SID',
							'input_type'  => 'text',
							'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
						),
						'twilio_auth_token'     => array(
							'label'       => 'Auth Token',
							'sublabel'    => 'Enter your Twilio Auth Token',
							'input_type'  => 'text',
							'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
						),
					)
				),
				'twilio-number' => array(
					'label'   => 'Sending Number',
					'options' => array(
						'twilio_from_number'     => array(
							'label'       => 'Twilio From Number',
							'sublabel'    => 'Enter the number, assigned by Twilio, that you want to send messages from',
							'input_type'  => 'text',
							'placeholder' => '+15555555555',
						),
					)
				)
			)
		);
	}

	public function global_settings_optional() {
		return array();
	}

	public function settings_required() {
		return array(
			'to' => array(
				'label'       => 'Send Text Message To',
				'sublabel'    => 'Enter the phone numbers that you want to send the notification to',
				'input_type'  => 'select',
				'placeholder' => 'Enter the phone numbers that you want to send the notification to',
				'selections'  => array()
			)
		);
	}

	public function settings_optional() {
		return array();
	}

	public function run_global_settings() {
		add_filter( 'nb_global_settings', array( $this, 'display_global_settings' ) );
	}

	public function display_global_settings( $settings ) {
		$settings[] = $this->global_settings_required();
		return $settings;
	}

	public static function send( $queue_id, $nb_id, $content ) {

		require_once( NB_TWILIO_DIR . 'includes/Services/Twilio.php' );

		if ( Global_Settings::get_instance()->get_value('twilio_account_sid') ) {
			$AccountSid = Global_Settings::get_instance()->get_value('twilio_account_sid');
		} else {
			return;
		}

		if ( Global_Settings::get_instance()->get_value('twilio_auth_token') ) {
			$AuthToken  = Global_Settings::get_instance()->get_value('twilio_auth_token');
		} else {
			return;
		}

		if ( Global_Settings::get_instance()->get_value('twilio_from_number') ) {
			$from_number  = Global_Settings::get_instance()->get_value('twilio_from_number');
		} else {
			return;
		}

		$client = new \Services_Twilio( $AccountSid, $AuthToken );


		$details = Notifications::get_instance()->get_notification_by_id( $nb_id );
		$options = json_decode( $details->options );

		$to_addresses = $options->service->required->to;



		if ( is_array( $to_addresses ) ) {
			foreach( $to_addresses as $to_address ) {
				$sms = $client->account->messages->sendMessage( $from_number, $to_address, $content );
			}
		}

		do_action('nb_sent', $queue_id, $nb_id);
	}

}

Services::register( new Twilio() );