<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class User extends Event {

	public $id = 'user';
	public $label = 'Users';

}

Events::register( new User() );