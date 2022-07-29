<?php

namespace NB_Gravity_Forms\Events;
use NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events\Event;

class EDD extends Event {

	public $id = 'edd';

	public function get_services() {
		return false;
	}

	public function character_limit() {
		return false;
	}

	public function settings_required() {
		return array();
	}

	public function settings_optional() {
		return array();
	}

	public function placeholder() {
		return 'Select your notification method';
	}

	public function label() {
		return 'Easy Digital Downloads';
	}

}

Events::register( new EDD() );