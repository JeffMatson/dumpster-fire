<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Permalink_Structure extends Trigger {

	public $id          = 'setting-permalink-structure';
	public $depends_on  = 'setting';
	public $placeholder = 'The permalink structure has been changed by {user}';
	public $label       = 'Permalink Structure';

	public function listeners() {
		add_action( 'update_option_permalink_structure', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Permalink_Structure() );