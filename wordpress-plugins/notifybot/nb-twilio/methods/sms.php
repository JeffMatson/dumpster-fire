<?php

namespace NotifyBot\Notifications\Methods;
use NotifyBot\Notifications\Methods;

class SMS extends Method {

	public $id = 'sms';

	public function depends_on() {
		return false;
	}

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
		return 'SMS';
	}

}

Methods::register( new SMS() );