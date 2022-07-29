<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Post extends Event {

	public $id = 'post';
	public $label = 'Posts';

}

Events::register( new Post() );