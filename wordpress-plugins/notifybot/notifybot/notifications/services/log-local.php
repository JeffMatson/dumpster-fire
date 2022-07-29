<?php

namespace NotifyBot\Notifications\Services;
use NotifyBot\Core;
use NotifyBot\Models\Global_Settings;
use NotifyBot\Models\Notifications;
use NotifyBot\Notifications\Services;
use Psr\Log\LogLevel;
use Katzgrau\KLogger\Logger;

class Log_Local extends Service {

	public $id = 'log-local';
	public $label = 'Local Logging';
	public $depends_on = 'log';

	public function local_settings() {
		return array(
			'log_dir' => array(
				'required'      => true,
				'label'         => 'Log Directory',
				'sublabel'      => '',
				'input_type'    => 'text',
				'placeholder'   => 'path/to/dir',
				'default_value' => self::generate_default_path(),
				'global_key'   => 'log_dir_'
			)
		);
	}

	public static function send( $queue_id, $nb_id, $content ) {

		$details = Notifications::get_instance()->get_notification_by_id( $nb_id );
		$options = json_decode( $details->options );

		$log_dir_path = $options->service->required->log_dir;

		if ( ! file_exists( $log_dir_path ) ) {
			wp_mkdir_p( $log_dir_path );
		}

		$log = new Logger( $log_dir_path, LogLevel::DEBUG );
		$log->info($content);

		do_action('nb_sent', $queue_id, $nb_id);
	}

	public static function generate_default_path() {
		$upload_dir = wp_upload_dir();
		$basename = trailingslashit( $upload_dir['basedir'] );
		$log_dir_name = 'notifybot_' . Core::get_instance()->generate_string();
		$full_path = $basename . $log_dir_name;
		
		$full_path = apply_filters( 'nb_default_log_dir_path', $full_path );

		return $full_path;
	}
	
}

Services::register( new Log_Local() );