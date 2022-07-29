<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Registration_Allowed extends Trigger {

	public $id          = 'setting-registration-allowed';
	public $depends_on  = 'setting';
	public $placeholder = 'User registration settings changed by {user}';
	public $label       = 'User Registration';

	public function listeners() {
		add_action( 'update_option_users_can_register', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Registration_Allowed() );