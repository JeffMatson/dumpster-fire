<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Comment_Edit extends Trigger {

	public $id          = 'comment-edit';
	public $depends_on  = 'comment';
	public $placeholder = 'Comment ID {comment_id} was edited by {user}';
	public $label       = 'Comment Edited';
	public $merge_tags  = array(
		'comment_id'
	);

	public function listeners() {
		add_action( 'edit_comment', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $id ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $id ) {
				$replacements['{comment_id}'] = $id;
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $id ) {

		$this->merge_tag_filters( $id );
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}
}

Triggers::register( new Comment_Edit() );
