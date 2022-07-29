<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Plugin_Install extends Trigger {

	public $id          = 'plugin-install';
	public $depends_on  = 'plugin';
	public $placeholder = 'Plugin {plugin_name} was installed by {user}';
	public $label       = 'Plugin Installed';
	public $merge_tags  = array(
		'plugin_name',
		'plugin_description',
		'plugin_version',
	);

	public function listeners() {
		add_action( 'install_plugin_complete_actions', array( $this, 'triggered' ), 10, 3 );
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

	public function triggered( $install_actions, $this_api, $plugin_file ) {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );

		$this->merge_tag_filters($plugin_data);
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Plugin_Install() );