<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Theme_Activate extends Trigger {

	public $id          = 'theme-activate';
	public $depends_on  = 'theme';
	public $placeholder = 'Theme {theme_name} has been activated by {user}';
	public $label       = 'Theme Activated';
	public $merge_tags  = array(
		'theme_name',
		'theme_version',
		'theme_description',
	);

	public function settings_optional() {
		return array(
			'theme' => array(
				'required'    => false,
				'label'       => 'Theme',
				'sublabel'    => 'If you want this to only monitor a specific theme, enter it here.',
				'input_type'  => 'select',
				'multiple' => true,
				'placeholder' => 'Select Theme',
				'selections'  => $this->get_themes()
			)
		);
	}

	public function merge_tag_filters( $current_theme ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $current_theme ) {
				$replacements['{theme_name}'] = $current_theme['Name'];
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $current_theme ) {
				$replacements['{theme_version}'] = $current_theme['Version'];
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $current_theme ) {
				$replacements['{theme_description}'] = $current_theme['Description'];
				return $replacements;
			}, 10, 1 );

	}

	public function listeners() {
		add_action( 'after_switch_theme', array( $this, 'triggered' ) );
	}

	public function triggered( $theme ) {

		$current_theme = wp_get_theme();

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $current_theme ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->page ) ) {
					if ( ! in_array( $current_theme['Name'], $optional->theme ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters($current_theme);

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Theme_Activate() );