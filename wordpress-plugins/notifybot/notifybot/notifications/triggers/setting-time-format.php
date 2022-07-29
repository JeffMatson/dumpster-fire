<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Time_Format extends Trigger {

	public $id          = 'setting-time-format';
	public $depends_on  = 'setting';
	public $placeholder = 'Time format changed by {user}';
	public $label       = 'Time Format';

	public function listeners() {
		add_action( 'update_option_time_format', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Time_Format() );