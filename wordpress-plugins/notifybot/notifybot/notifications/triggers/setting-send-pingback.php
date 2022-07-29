<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Send_Pingback extends Trigger {

	public $id          = 'setting-send-pingback';
	public $depends_on  = 'setting';
	public $placeholder = 'Pingback settings were changed by {user}';
	public $label       = 'Pingback Sending';

	public function listeners() {
		add_action( 'update_option_default_pingback_flag', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Send_Pingback() );