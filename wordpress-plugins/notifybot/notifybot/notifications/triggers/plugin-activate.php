<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Plugin_Activate extends Trigger {

	public $id          = 'plugin-activate';
	public $depends_on  = 'plugin';
	public $placeholder = 'Plugin {plugin_name} was activated by {user}';
	public $label       = 'Plugin Activated';
	public $merge_tags  = array(
		'plugin_name',
		'plugin_description',
		'plugin_version',
	);

	public function local_settings() {
		return array(
			'plugin' => array(
				'required'    => false,
				'label'       => 'Plugin',
				'sublabel'    => 'If you want this to only monitor a specific plugin, enter it here.',
				'input_type'  => 'select',
				'multiple'    => true,
				'placeholder' => 'Select Plugin',
				'selections'  => $this->get_plugins()
			)
		);
	}

	public function listeners() {
		add_action( 'activated_plugin', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters($plugin_data) {
		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $plugin_data ) {
				$replacements['{plugin_name}'] = $plugin_data['Name'];
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $plugin_data ) {
				$replacements['{plugin_version}'] = $plugin_data['Version'];
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $plugin_data ) {
				$replacements['{plugin_description}'] = $plugin_data['Description'];
				return $replacements;
			}, 10, 1 );
	}

	public function triggered( $plugin, $network_wide ) {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $plugin_data ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->page ) ) {
					if ( ! in_array( $plugin_data['Name'], $optional->plugin ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters($plugin_data);
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Plugin_Activate() );