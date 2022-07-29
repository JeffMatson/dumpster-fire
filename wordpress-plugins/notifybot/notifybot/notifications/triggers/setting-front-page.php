<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Front_Page extends Trigger {

	public $id          = 'setting-front-page';
	public $depends_on  = 'setting';
	public $placeholder = 'Front page changed by {user}';
	public $label       = 'Front Page';

	public function listeners() {
		add_action( 'update_option_page_on_front', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Front_Page() );