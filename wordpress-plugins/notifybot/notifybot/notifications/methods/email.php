<?php

namespace NotifyBot\Notifications\Methods;
use NotifyBot\Notifications\Methods;

class Email extends Method {

	public $id = 'email';
	public $label = 'Email';

}

Methods::register( new Email() );