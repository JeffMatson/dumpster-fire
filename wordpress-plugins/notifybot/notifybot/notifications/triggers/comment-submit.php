<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Comment_Submit extends Trigger {

	public $id          = 'comment-submit';
	public $depends_on  = 'comment';
	public $placeholder = 'A new comment was submitted by {comment_author} on post ID {comment_post_id}';
	public $label       = 'Comment Submitted';
	public $merge_tags  = array(
		'comment_author',
		'comment_post_id',
		'comment_id',
	);

	public function listeners() {
		add_action( 'wp_insert_comment', array( $this, 'triggered' ), 10, 2 );
	}

	public function merge_tag_filters( $id, $comment ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $id ) {
				$replacements['{comment_id}'] = $id;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $comment ) {
				$replacements['{comment_author}'] = $comment->comment_author;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $comment ) {
				$replacements['{comment_post_id}'] = $comment->comment_post_id;
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $id, $comment ) {

		$this->merge_tag_filters( $id, $comment );
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}
}

Triggers::register( new Comment_Submit() );
