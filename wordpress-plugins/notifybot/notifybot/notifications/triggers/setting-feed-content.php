<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Feed_Content extends Trigger {

	public $id          = 'setting-feed-content';
	public $depends_on  = 'setting';
	public $placeholder = 'RSS feed content changed by {user}';
	public $label       = 'RSS Feed Content';

	public function listeners() {
		add_action( 'update_option_rss_use_excerpt', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Feed_Content() );