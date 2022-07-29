<?php

namespace NB_Gravity_Forms\Events;
use NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Events\Event;

class Gravity_Forms extends Event {

	public $id = 'gravity-forms';

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
		return 'Gravity Forms';
	}

}

Events::register( new Gravity_Forms() );