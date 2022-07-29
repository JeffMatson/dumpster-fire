<?php

namespace NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events;

class Login extends Event {

	public $id = 'login';
	public $label = 'Logins';

}

Events::register( new Login() );