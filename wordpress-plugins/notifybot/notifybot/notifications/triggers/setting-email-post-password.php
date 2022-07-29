<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Email_Post_Password extends Trigger {

	public $id          = 'setting-email-post-password';
	public $depends_on  = 'setting';
	public $placeholder = 'Email posting password changed by {user}';
	public $label       = 'Email Posting Password';

	public function listeners() {
		add_action( 'update_option_mailserver_pass', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Email_Post_Password() );