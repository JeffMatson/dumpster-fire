<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Week_Start extends Trigger {

	public $id          = 'setting-week-start';
	public $depends_on  = 'setting';
	public $placeholder = 'Week start changed by {user}';
	public $label       = 'Week Start';

	public function listeners() {
		add_action( 'update_option_start_of_week', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Week_Start() );