<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Search_Engine_Visibility extends Trigger {

	public $id          = 'setting-search-engine-visibility';
	public $depends_on  = 'setting';
	public $placeholder = 'Search engine visibility changed by {user}';
	public $label       = 'Search Engine Visibility';

	public function listeners() {
		add_action( 'update_option_blog_public', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Search_Engine_Visibility() );