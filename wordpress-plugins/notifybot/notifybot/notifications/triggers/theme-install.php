<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Theme_Install extends Trigger {

	public $id          = 'theme-install';
	public $depends_on  = 'theme';
	public $placeholder = 'Theme {theme_name} was installed by {user}';
	public $label       = 'Theme Installed';
	public $merge_tags  = array(
		'theme_name',
		'theme_version',
		'theme_description'
	);

	public function listeners() {
		add_action( 'install_theme_complete_actions', array( $this, 'triggered' ), 10, 4 );
	}

	public function merge_tag_filters( $theme_info ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $theme_info ) {
				$replacements['{theme_name}'] = $theme_info['Name'];
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $theme_info ) {
				$replacements['{theme_version}'] = $theme_info['Version'];
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $theme_info ) {
				$replacements['{theme_description}'] = $theme_info['Description'];
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $install_actions, $this_api, $stylesheet, $theme_info ) {

		$this->merge_tag_filters($theme_info);

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Theme_Install() );