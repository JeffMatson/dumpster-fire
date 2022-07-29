<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Site_Language extends Trigger {

	public $id          = 'setting-site-language';
	public $depends_on  = 'setting';
	public $placeholder = 'Site language changed by {user}';
	public $label       = 'Site Language';

	public function listeners() {
		add_action( 'update_option_WPLANG', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Site_Language() );