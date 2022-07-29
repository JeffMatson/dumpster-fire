<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;


class Theme_Customize extends Trigger {

	public $id = 'theme-customize';
	public $depends_on  = 'theme';
	public $placeholder = 'Theme was edited in the customizer by {user}';
	public $label       = 'Theme Customizer Settings Changed';

	public function listeners() {
		add_action( 'customize_save_after', array( $this, 'triggered' ) );
	}

	public function triggered() {

		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
	}
}

Triggers::register( new Theme_Customize() );
