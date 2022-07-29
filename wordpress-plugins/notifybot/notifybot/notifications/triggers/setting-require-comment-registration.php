<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Require_Comment_Registration extends Trigger {

	public $id          = 'setting-require-comment-registration';
	public $depends_on  = 'setting';
	public $placeholder = 'Comment registration requirements were changed by {user}';
	public $label       = 'Comment Registration';

	public function listeners() {
		add_action( 'update_option_comment_registration', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Require_Comment_Registration() );