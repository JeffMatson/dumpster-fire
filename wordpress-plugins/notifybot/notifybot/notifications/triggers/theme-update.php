<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Theme_Update extends Trigger {

	public $id          = 'theme-update';
	public $depends_on  = 'theme';
	public $placeholder = 'Theme {theme_name} was updated to version {theme_version} by {user}';
	public $label       = 'Theme Updated';
	public $merge_tags  = array(
		'theme_name',
		'theme_version',
		'theme_description'
	);

	public function settings_optional() {
		return array(
			'theme' => array(
				'required'    => false,
				'label'       => 'Theme',
				'sublabel'    => 'If you want this to only monitor a specific theme, enter it here.',
				'input_type'  => 'select',
				'multiple'    => true,
				'placeholder' => 'Select Theme',
				'selections'  => $this->get_themes()
			)
		);
	}

	public function listeners() {
		add_action( 'update_theme_complete_actions', array( $this, 'triggered' ), 10, 2 );
		add_action( 'update_bulk_theme_complete_actions', array( $this, 'triggered' ), 10, 2 );
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

	public function triggered( $update_actions, $theme_info ) {

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $theme_info ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->page ) ) {
					if ( ! in_array( $theme_info['Name'], $optional->theme ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters( $theme_info );

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}
}

Triggers::register( new Theme_Update() );
