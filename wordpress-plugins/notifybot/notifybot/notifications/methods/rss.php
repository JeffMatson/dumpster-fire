<?php

namespace NotifyBot\Notifications\Methods;
use NotifyBot\Notifications\Methods;

class RSS extends Method {

	public $id = 'rss';
	public $label = 'RSS Feed';

}

Methods::register( new RSS() );