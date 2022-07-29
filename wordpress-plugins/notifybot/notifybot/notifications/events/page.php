<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Page extends Event {

	public $id = 'page';
	public $label = 'Pages';

}

Events::register( new Page() );