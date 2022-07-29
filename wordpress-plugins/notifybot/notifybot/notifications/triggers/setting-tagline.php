<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Tagline extends Trigger {

	public $id          = 'setting-tagline-change';
	public $depends_on  = 'setting';
	public $placeholder = 'Tagline was changed by user {user}';
	public $label       = 'Tagline';

	public function listeners() {
		add_action( 'update_option_blogdescription', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Tagline() );