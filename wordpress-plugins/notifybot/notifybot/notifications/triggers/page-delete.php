<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Page_Delete extends Trigger {

	public $id          = 'page-delete';
	public $depends_on  = 'page';
	public $placeholder = 'Page {page_title} has been deleted by {user}';
	public $label       = 'Page Deleted';
	public $merge_tags  = array(
		'page_id',
		'page_title',
		'page_name',
	);

	public function local_settings() {
		return array(
			'page' => array(
				'required'    => false,
				'label'       => 'Page',
				'sublabel'    => 'If you want this to only monitor a specific page, select it here.',
				'input_type'  => 'select',
				'multiple'    => true,
				'placeholder' => 'Select Page',
				'selections'  => $this->get_optional_page()
			)
		);
	}

	public function listeners() {
		add_action( 'before_delete_post', array( $this, 'triggered' ), 10, 1 );
	}

	public function merge_tag_filters( $post_details ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_details ) {
				$replacements['{page_id}'] = $post_details->ID;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_details ) {
				$replacements['{page_title}'] = $post_details->post_title;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_details ) {
				$replacements['{page_name}'] = $post_details->post_name;
				return $replacements;
			}, 10, 1 );

	}

	public function triggered( $post_id ) {

		$post_details = get_post( $post_id );

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $post_details ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->page ) ) {
					if ( ! in_array( $post_details->post_name, $optional->page ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		if ( $post_details->post_type != 'page' || $post_details->post_type == 'revision' || $post_details->post_type == 'nav_menu_item' ) {
			return;
		}

		$this->merge_tag_filters($post_details);
		Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );

	}

}

Triggers::register( new Page_Delete() );