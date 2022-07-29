<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Avatar_Rating extends Trigger {

	public $id          = 'setting-avatar-rating';
	public $depends_on  = 'setting';
	public $placeholder = 'Avatar rating settings have been changed by {user}';
	public $label       = 'Avatar Rating';

	public function listeners() {
		add_action( 'update_option_avatar_rating', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Avatar_Rating() );