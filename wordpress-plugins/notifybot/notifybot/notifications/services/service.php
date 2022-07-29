<?php

namespace NotifyBot\Notifications\Services;

class Service {

	public $id;
	public $label;
	public $depends_on;
	public $local_settings;
	public $global_settings;
	
	public function __construct() {
		if ( method_exists( $this, 'local_settings' ) )
			$this->local_settings = $this->local_settings();

		if ( method_exists( $this, 'global_settings' ) )
			$this->global_settings = $this->global_settings();
	}

}