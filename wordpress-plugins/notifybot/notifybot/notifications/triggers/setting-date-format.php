<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Date_Format extends Trigger {

	public $id          = 'setting-date-format';
	public $depends_on  = 'setting';
	public $placeholder = 'Date format changed by {user}';
	public $label       = 'Date Format';

	public function listeners() {
		add_action( 'update_option_date_format', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Date_Format() );