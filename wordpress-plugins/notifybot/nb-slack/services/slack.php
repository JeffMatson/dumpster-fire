<?php

namespace NotifyBot\Notifications\Services;
use Maknz\Slack\Client;
use NotifyBot\Models\Global_Settings;
use NotifyBot\Notifications\Services;
use NotifyBot\Models\Notifications;
use NotifyBot\Notifications\Triggers;

class Slack extends Service {

	public $id = 'slack';

	public function label() {
		return 'Slack';
	}

	public function depends_on() {
		return array( 'chat' );
	}

	public function character_limit() {
		return false;
	}

	public function global_settings_required() {
		return array(
			'id'      => $this->id,
			'header'  => 'Slack Settings',
			'sections' => array(
				'twilio-api-keys' => array(
					'label'   => 'Slack API Endpoint',
					'options' => array(
						'slack_endpoint'     => array(
							'label'       => 'API Endpoint',
							'sublabel'    => 'Enter your Slack endpoint',
							'input_type'  => 'text',
							'placeholder' => 'https://hooks.slack.com/services/xxxxxx/xxxxxxx/xxxxxxxxxxx',
						),
					)
				),
			)
		);
	}

	public function global_settings_optional() {
		return array();
	}

	public function settings_required() {
		return array();
	}

	public function settings_optional() {
		return array(
			'to' => array(
				'label'       => 'User or channel (prefix users with @ and channels with #)',
				'sublabel'    => 'Enter your channel or user to send this message to',
				'input_type'  => 'text',
				'placeholder' => 'prefix users with @ and channels with #',
			)
		);
	}

	public function run_global_settings() {
		add_filter( 'nb_global_settings', array( $this, 'display_global_settings' ) );
	}

	public function display_global_settings( $settings ) {
		$settings[] = $this->global_settings_required();
		return $settings;
	}

	public static function send( $queue_id, $nb_id, $content ) {

		require_once( NB_SLACK_DIR . 'includes/vendor/autoload.php' );

		if ( Global_Settings::get_instance()->get_value('slack_endpoint') ) {
			$endpoint  = Global_Settings::get_instance()->get_value('slack_endpoint');
		} else {
			return;
		}

		if ( Global_Settings::get_instance()->get_value('slack_username') ) {
			$username = Global_Settings::get_instance()->get_value('slack_username');
		} else {
			$username = 'NotifyBot';
		}

		if ( Global_Settings::get_instance()->get_value('slack_default_channel') ) {
			$channel  = Global_Settings::get_instance()->get_value('slack_default_channel');
		} else {
			$channel = null;
		}

		if ( Global_Settings::get_instance()->get_value('slack_icon') ) {
			$icon  = Global_Settings::get_instance()->get_value('slack_icon');
		} else {
			$icon = 'http://dev.notifybot.io/wp-content/uploads/2016/04/notifybot-slack-2.png';
		}

		$details = Notifications::get_instance()->get_notification_by_id( $nb_id );
		$options = json_decode( $details->options );
		$optional = $options->service->optional;

		if ( property_exists( $optional, 'to' ) ) {
			$channel = $optional->to;
		}

		$slack_settings = array (
			'username'   => $username,
			'channel'    => $channel,
			'link_names' => true,
			'icon'       => $icon
		);

		$client = new Client( $endpoint, $slack_settings );

		$client->send( $content );

		do_action('nb_sent', $queue_id, $nb_id);
	}

}

Services::register( new Slack() );