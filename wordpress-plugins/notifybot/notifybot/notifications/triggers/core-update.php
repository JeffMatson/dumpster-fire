<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Core_Update extends Trigger {

	public $id          = 'core-update';
	public $depends_on  = 'core';
	public $placeholder = 'WordPress was successfully updated to version {version}';
	public $label       = 'Core Updates';
	public $merge_tags  = array(
		'version',
	);

	public function listeners() {
		add_action( 'core_updated_successfully', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $wp_version ) {
		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $wp_version ) {
					$replacements['{version}'] = $wp_version;
				return $replacements;
			}, 10, 2 );
	}

	public function triggered( $wp_version ) {

		$this->merge_tag_filters($wp_version);
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
		
	}

}

Triggers::register( new Core_Update() );