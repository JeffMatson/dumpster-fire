<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Post_New extends Trigger {

	public $id          = 'post-new';
	public $depends_on  = 'post';
	public $placeholder = 'A new post {post_title} has been created by {user}';
	public $label       = 'New Post';
	public $merge_tags  = array(
		'post_id',
		'post_title',
		'post_name',
		'post_type',
	);

	public function local_settings() {
		return array(
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

	public function merge_tag_filters( $post ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{post_id}'] = $post->ID;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{post_title}'] = $post->post_title;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{post_name}'] = $post->post_name;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{post_type}'] = $post->post_type;
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $post_id, $post, $update ) {

		if ( $post->post_type == 'page' || wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post->post_status != 'auto-draft' )  {
			return;
		}

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $post ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->post_type ) ) {
					if ( ! in_array( $post->post_type, $optional->post ) ) {
						$allowed = false;
					}
				} elseif ( $post->post_type !== 'post' ) {
					$allowed = false;
				}

				return $allowed;
			}, 10, 2
		);

		if ( wp_is_post_revision($post_id) == false ) {
			update_post_meta( $post_id, 'nb_new_post', true );
		}
	}

}

Triggers::register( new Post_New() );