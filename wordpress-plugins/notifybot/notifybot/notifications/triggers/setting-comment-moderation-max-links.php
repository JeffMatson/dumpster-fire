<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Comment_Moderation_Max_Links extends Trigger {

	public $id          = 'setting-comment-moderation-max-links';
	public $depends_on  = 'setting';
	public $placeholder = 'The max comment links before moderation setting was changed by {user}';
	public $label       = 'Comment Moderation Max Links';

	public function listeners() {
		add_action( 'update_option_comment_max_links', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Comment_Moderation_Max_Links() );