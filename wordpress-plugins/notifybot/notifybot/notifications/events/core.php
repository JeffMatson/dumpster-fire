<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Core extends Event {

	public $id = 'core';
	public $label = 'WordPress Core';

}

Events::register( new Core() );