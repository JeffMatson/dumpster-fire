<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Media_Delete extends Trigger {

	public $id          = 'media-delete';
	public $depends_on  = 'media';
	public $placeholder = 'Media deleted by: {user}';
	public $label       = 'Media Deleted';
	public $merge_tags  = array();

	public function listeners() {
		add_action( 'delete_attachment', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Media_Delete() );