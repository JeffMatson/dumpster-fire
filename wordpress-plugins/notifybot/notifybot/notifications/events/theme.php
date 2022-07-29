<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Theme extends Event {

	public $id = 'theme';
	public $label = 'Themes';

}

Events::register( new Theme() );