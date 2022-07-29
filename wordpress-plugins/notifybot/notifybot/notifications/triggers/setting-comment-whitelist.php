<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Comment_Whitelist extends Trigger {

	public $id          = 'setting-comment-moderation-notification';
	public $depends_on  = 'setting';
	public $placeholder = 'Comment whitelisting settings were changed by {user}';
	public $label       = 'Comment Whitelisting';

	public function listeners() {
		add_action( 'update_option_comment_whitelist', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Comment_Whitelist() );