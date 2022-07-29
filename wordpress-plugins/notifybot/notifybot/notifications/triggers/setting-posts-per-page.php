<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Posts_Per_Page extends Trigger {

	public $id          = 'setting-posts-per-page';
	public $depends_on  = 'setting';
	public $placeholder = 'Posts per page changed by {user}';
	public $label       = 'Posts Per Page';

	public function listeners() {
		add_action( 'update_option_posts_per_page', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Posts_Per_Page() );