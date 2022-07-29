<?php

namespace NotifyBot\Notifications\Methods;
use NotifyBot\Notifications\Methods;

class Webhook extends Method {

	public $id = 'webhook';
	public $label = 'Webhooks';

}

Methods::register( new Webhook() );