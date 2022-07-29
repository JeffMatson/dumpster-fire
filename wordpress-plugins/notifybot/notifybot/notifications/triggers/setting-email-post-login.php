<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Email_Post_Login extends Trigger {

	public $id          = 'setting-email-post-login';
	public $depends_on  = 'setting';
	public $placeholder = 'Email posting login changed by {user}';
	public $label       = 'Email Posting Login';

	public function listeners() {
		add_action( 'update_option_mailserver_login', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Email_Post_Login() );