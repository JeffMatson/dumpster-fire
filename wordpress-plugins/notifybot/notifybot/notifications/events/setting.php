<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Setting extends Event {

	public $id = 'setting';
	public $label = 'Settings';

}

Events::register( new Setting() );