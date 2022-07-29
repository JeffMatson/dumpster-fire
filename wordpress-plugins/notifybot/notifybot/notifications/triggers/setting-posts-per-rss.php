<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Setting_Posts_Per_RSS extends Trigger {

	public $id          = 'setting-posts-per-rss';
	public $depends_on  = 'setting';
	public $placeholder = 'Posts per RSS feed changed by {user}';
	public $label       = 'Posts Per RSS Feed';

	public function listeners() {
		add_action( 'update_option_posts_per_rss', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Setting_Posts_Per_RSS() );