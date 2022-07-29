<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Default_Avatar extends Trigger {

	public $id          = 'setting-default-avatar';
	public $depends_on  = 'setting';
	public $placeholder = 'The default avatar has been changed by {user}';
	public $label       = 'Default Avatar';

	public function listeners() {
		add_action( 'update_option_avatar_default', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Default_Avatar() );