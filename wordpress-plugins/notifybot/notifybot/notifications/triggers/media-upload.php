<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Media_Upload extends Trigger {

	public $id          = 'media-upload';
	public $depends_on  = 'media';
	public $placeholder = 'Media uploaded by: {user}';
	public $label       = 'Media Uploaded';
	public $merge_tags  = array();

	public function listeners() {
		add_action( 'admin_action_upload-attachment', array( $this, 'triggered' ) );
	}

	public function triggered() {
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}

}

Triggers::register( new Media_Upload() );