<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Post_Update extends Trigger {

	public $id          = 'post-update';
	public $depends_on  = 'post';
	public $placeholder = 'A post {post_title} has been updated by {user}';
	public $label       = 'Post Updated';
	public $merge_tags  = array(
		'post_id',
		'post_title',
		'post_name',
		'post_type',
	);

	public function depends_on() {
		return array( 'post' );
	}

	public function settings_optional() {
		return array(
			'post'      => array(
				'required'    => false,
				'label'       => 'Post',
				'sublabel'    => 'If you want this to only monitor a specific post, select it here.',
				'input_type'  => 'select',
				'multiple'    => true,
				'placeholder' => 'Select Post',
				'selections'  => $this->get_optional_post()
			),
			'post_type' => array(
				'required'    => false,
				'label'       => 'Post Type',
				'sublabel'    => 'If you want this to only monitor a specific post type, select it here.',
				'input_type'  => 'select',
				'multiple'    => true,
				'placeholder' => 'Select Post Type',
				'selections'  => $this->get_optional_post_type()
			)
		);
	}

	public function listeners() {
		add_action( 'wp_insert_post', array( $this, 'triggered' ), 10, 3 );
	}

	public function merge_tag_filters( $post_data ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{post_id}'] = $post_data->ID;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{post_title}'] = $post_data->post_title;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{post_name}'] = $post_data->post_name;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{post_type}'] = $post_data->post_type;
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $post_id, $post_data, $update ) {

		if ( ! wp_is_post_revision($post_id) == false || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post_data->post_type == 'page' || $post_data->post_status == 'auto-draft' || $post_data->post_status == 'trash') {
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
				} elseif ( $post_data->post_type !== 'post' ) {
					$allowed = false;
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters($post_data);

		if ( get_post_meta( $post_id, 'nb_new_post' ) ) {
			Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( 'post-new' ) );

			do_action( 'queued_post-new' );
			delete_post_meta( $post_id, 'nb_new_post' );
		} else {
			Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
			do_action( 'queued_' . $this->id );
		}
	}

}

Triggers::register( new Post_Update() );