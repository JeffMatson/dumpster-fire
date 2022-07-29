<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Plugin extends Event {

	public $id = 'plugin';
	public $label = 'Plugins';

}

Events::register( new Plugin() );