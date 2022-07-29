<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Require_Name_Email extends Trigger {

	public $id          = 'setting-require-name-email';
	public $depends_on  = 'setting';
	public $placeholder = 'Comment name/email requirements were changed by {user}';
	public $label       = 'Comment Name/Email Requirement';

	public function listeners() {
		add_action( 'update_option_require_name_email', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Require_Name_Email() );