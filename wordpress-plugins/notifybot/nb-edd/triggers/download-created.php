<?php

namespace NB_EDD\Triggers;
use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Notifications\Triggers\Trigger;

class Download_Created extends Trigger {

	public $id = 'edd-download-created';

	public function depends_on() {
		return array( 'edd' );
	}

	public function global_settings_required() {
		return array();
	}

	public function settings_required() {
		return array();
	}

	public function settings_optional() {
		return array();
	}

	public function label() {
		return 'Download Created';
	}

	public function placeholder() {
		return 'Download {download_title} was created by {user}';
	}

	public function merge_tags() {
		return array(
			'download_id',
			'download_title',
			'download_name'
		);
	}

	public function listeners() {
		add_action( 'wp_insert_post', array( $this, 'triggered' ), 10, 3 );
	}

	public function merge_tag_filters( $post_id, $post, $update ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{download_id}'] = $post->ID;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{download_title}'] = $post->post_title;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{download_name}'] = $post->post_name;
				return $replacements;
			}, 10, 1 );
	}

	public function triggered( $post_id, $post, $update ) {


		if ( $post->post_type !== 'download' || wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( $post->post_status !== 'auto-draft' ) )  {
			return;
		}

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $post ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->post_type ) ) {
					if ( ! in_array( $post->post_type, $optional->post ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		update_post_meta( $post_id, 'nb_new_post', true );

	}

}

Triggers::register( new Download_Created() );