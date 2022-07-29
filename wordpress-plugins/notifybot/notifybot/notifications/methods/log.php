<?php

namespace NotifyBot\Notifications\Methods;
use NotifyBot\Notifications\Methods;

class Log extends Method {

	public $id = 'log';
	public $label = 'Output To Log';

}

Methods::register( new Log() );