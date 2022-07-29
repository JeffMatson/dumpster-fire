<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Automatic_Comment_Closed extends Trigger {

	public $id          = 'setting-automatic-comment-closed';
	public $depends_on  = 'setting';
	public $placeholder = 'Automatic comment closing settings were changed by {user}';
	public $label       = 'Automatic Comment Closing';

	public function listeners() {
		add_action( 'update_option_close_comments_for_old_posts', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Automatic_Comment_Closed() );