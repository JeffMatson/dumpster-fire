<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Default_Role extends Trigger {

	public $id          = 'setting-default-role';
	public $depends_on  = 'setting';
	public $placeholder = 'Default user role was changed by {user}';
	public $label       = 'Default User Role';

	public function listeners() {
		add_action( 'update_option_default_role', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Default_Role() );