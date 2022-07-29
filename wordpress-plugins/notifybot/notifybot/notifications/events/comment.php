<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Comment extends Event {

	public $id = 'comment';
	public $label = 'Comments';

}

Events::register( new Comment() );