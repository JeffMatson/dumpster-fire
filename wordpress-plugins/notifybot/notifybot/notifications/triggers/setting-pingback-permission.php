<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Pingback_Permission extends Trigger {

	public $id          = 'setting-pingback-permission';
	public $depends_on  = 'setting';
	public $placeholder = 'Pingback settings were changed by {user}';
	public $label       = 'Allow/Disallow Pingbacks';

	public function listeners() {
		add_action( 'update_option_default_ping_status', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Pingback_Permission() );