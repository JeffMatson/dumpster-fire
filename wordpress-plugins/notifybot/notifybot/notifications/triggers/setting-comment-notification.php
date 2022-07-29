<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Comment_Notification extends Trigger {

	public $id          = 'setting-comment-notification';
	public $depends_on  = 'setting';
	public $placeholder = 'New comment notification settings were changed by {user}';
	public $label       = 'New Comment Notification (WordPress)';

	public function listeners() {
		add_action( 'update_option_comments_notify', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Comment_Notification() );