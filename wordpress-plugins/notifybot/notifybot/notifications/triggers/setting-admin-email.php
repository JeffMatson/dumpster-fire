<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Admin_Email extends Trigger {

	public $id          = 'setting-admin-email';
	public $depends_on  = 'setting';
	public $placeholder = 'Admin Email was changed by {user}';
	public $label       = 'Admin Email';

	public function listeners() {
		add_action( 'update_option_admin_email', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Admin_Email() );