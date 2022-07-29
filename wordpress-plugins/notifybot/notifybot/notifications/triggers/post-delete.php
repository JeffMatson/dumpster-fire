<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Post_Delete extends Trigger {

	public $id          = 'post-delete';
	public $depends_on  = 'post';
	public $placeholder = 'Post {post_title} has been deleted by {user}';
	public $label       = 'Post Deleted';
	public $merge_tags  = array(
		'post_id',
		'post_title',
		'post_name',
		'post_type'
	);

	public function local_settings() {
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
		add_action( 'before_delete_post', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $post_details ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_details ) {
				$replacements['{post_id}'] = $post_details->ID;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_details ) {
				$replacements['{post_title}'] = $post_details->post_title;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_details ) {
				$replacements['{post_name}'] = $post_details->post_name;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_details ) {
				$replacements['{post_type}'] = $post_details->post_type;
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $post_id ) {

		$post_details = get_post( $post_id );

		if ( $post_details->post_type == 'page' || $post_details->post_type == 'revision' || $post_details->post_type == 'nav_menu_item' ) {
			return;
		}

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $post_details ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->post ) ) {
					if ( ! in_array( $post_details->post_name, $optional->post ) ) {
						$allowed = false;
					}
				}
				if ( isset( $optional->post_type ) ) {
					if ( ! in_array( $post_details->post_type, $optional->post_type ) ) {
						$allowed = false;
					}
				} elseif ( $post_details->post_type !== 'post' ) {
					$allowed = false;
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters($post_details);
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );

	}

}

Triggers::register( new Post_Delete() );