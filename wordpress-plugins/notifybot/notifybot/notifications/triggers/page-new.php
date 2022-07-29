<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Page_New extends Trigger {

	public $id          = 'page-new';
	public $depends_on  = 'page';
	public $placeholder = 'A new page {page_title} has been created by {user}';
	public $label       = 'New Page';
	public $merge_tags  = array(
		'page_id',
		'page_title',
		'page_name',
	);

	public function listeners() {
		add_action( 'save_post', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $post ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{page_id}'] = $post->ID;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{page_title}'] = $post->post_title;

				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post ) {
				$replacements['{page_name}'] = $post->post_name;

				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $post_id, $post ) {

		if ( $post->post_type != 'page' || wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( $post->post_status !== 'auto-draft' ) )  {
			return;
		}

		if ( wp_is_post_revision($post_id) == false ) {
			update_post_meta( $post_id, 'nb_new_post', true );
		}
	}
}

Triggers::register( new Page_New() );