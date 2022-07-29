<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Media extends Event {

	public $id = 'media';
	public $label = 'Media';

}

Events::register( new Media() );