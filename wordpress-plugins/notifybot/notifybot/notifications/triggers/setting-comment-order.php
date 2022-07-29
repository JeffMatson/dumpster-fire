<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Comment_Order extends Trigger {

	public $id          = 'setting-comment-order';
	public $depends_on  = 'setting';
	public $placeholder = 'The comment order was changed by {user}';
	public $label       = 'Comment Ordering';

	public function listeners() {
		add_action( 'update_option_comment_order', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Comment_Order() );