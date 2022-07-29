<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Update_Services extends Trigger {

	public $id          = 'setting-update-services';
	public $depends_on  = 'setting';
	public $placeholder = 'Post update services changed by {user}';
	public $label       = 'Post Update Services';

	public function listeners() {
		add_action( 'update_option_ping_sites', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Update_Services() );