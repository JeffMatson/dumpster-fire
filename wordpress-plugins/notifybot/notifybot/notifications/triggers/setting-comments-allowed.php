<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Comments_Allowed extends Trigger {

	public $id          = 'setting-comments-allowed';
	public $depends_on  = 'setting';
	public $placeholder = 'Comment permissions were changed by {user}';
	public $label       = 'Allow/Disallow Comments';

	public function listeners() {
		add_action( 'update_option_default_comment_status', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Comments_Allowed() );