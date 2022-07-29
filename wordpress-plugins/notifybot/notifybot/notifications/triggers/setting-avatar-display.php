<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Avatar_Display extends Trigger {

	public $id          = 'setting-avatar-display';
	public $depends_on  = 'setting';
	public $placeholder = 'Avatar display settings have been changed by {user}';
	public $label       = 'Avatar Display';

	public function listeners() {
		add_action( 'update_option_show_avatar', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Avatar_Display() );