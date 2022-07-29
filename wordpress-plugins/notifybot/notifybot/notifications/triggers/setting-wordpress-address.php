<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_WordPress_Address extends Trigger {

	public $id          = 'setting-wordpress-address';
	public $depends_on  = 'setting';
	public $placeholder = 'WordPress Address was changed by user {user}';
	public $label       = 'WordPress Address';

	public function listeners() {
		add_action( 'update_option_siteurl', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_WordPress_Address() );