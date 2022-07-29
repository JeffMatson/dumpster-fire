<?php

namespace NB_EDD\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Download_Updated extends Triggers\Trigger {

	public $id = 'edd-download-updated';

	public function depends_on() {
		return array( 'edd' );
	}

	public function settings_optional() {
		return array();
	}

	public function get_optional_post() {
		$posts     = get_posts();
		$post_list = array();
		foreach ( $posts as $post ) {
			$post_list[] = $post->post_title;
		}

		return $post_list;
	}

	public function placeholder() {
		return 'Download {download_title} was updated by {user}';
	}

	public function label() {
		return 'Download Updated';
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

	public function merge_tag_filters( $post_data ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{download_id}'] = $post_data->ID;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{download_title}'] = $post_data->post_title;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{download_name}'] = $post_data->post_name;
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $post_id, $post_data, $update ) {

		if ( ! wp_is_post_revision($post_id) == false || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post_data->post_type !== 'download' || $post_data->post_status == 'auto-draft' || $post_data->post_status == 'trash') {
			return;
		}

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $post_data ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->post ) ) {
					if ( ! in_array( $post_data->post_name, $optional->post ) ) {
						$allowed =  false;
					}
				}

				if ( isset( $optional->post ) ) {
					if ( ! in_array( $post_data->post_type, $optional->post_type ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters($post_data);

		if ( get_post_meta( $post_id, 'nb_new_post' ) ) {
			Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( 'edd-download-created' ) );

			do_action( 'queued_edd-download-created' );
			delete_post_meta( $post_id, 'nb_new_post' );
		} else {
			Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
			do_action( 'queued_' . $this->id );
		}
	}

}

Triggers::register( new Download_Updated() );