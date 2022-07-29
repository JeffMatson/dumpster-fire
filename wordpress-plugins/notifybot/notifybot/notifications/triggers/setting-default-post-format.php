<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Default_Post_Format extends Trigger {

	public $id          = 'setting-default-post-format';
	public $depends_on  = 'setting';
	public $placeholder = 'Default post format changed by {user}';
	public $label       = 'Default Post Format';

	public function listeners() {
		add_action( 'update_option_default_post_format', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Default_Post_Format() );