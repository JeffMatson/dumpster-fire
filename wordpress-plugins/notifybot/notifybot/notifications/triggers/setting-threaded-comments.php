<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Threaded_Comments extends Trigger {

	public $id          = 'setting-threaded-comments';
	public $depends_on  = 'setting';
	public $placeholder = 'Threaded comment settings were changed by {user}';
	public $label       = 'Threaded Comments';

	public function listeners() {
		add_action( 'update_option_thread_comments', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Threaded_Comments() );