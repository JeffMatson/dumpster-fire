<?php

namespace NotifyBot\Notifications\Triggers;

use NotifyBot\Models\Notifications;
use NotifyBot\Models\Queue;
use NotifyBot\Notifications\Triggers;

class Page_Update extends Trigger {

	public $id          = 'page-update';
	public $depends_on  = 'page';
	public $placeholder = 'A page {page_title} was updated by {user}';
	public $label       = 'Page Updated';
	public $merge_tags  = array(
		'page_id',
		'page_title',
		'page_name',
	);

	public function settings_optional() {
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
		add_action( 'save_post', array( $this, 'triggered' ), 9, 3 );
	}

	public function merge_tag_filters( $post_data ) {

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{page_id}'] = $post_data->ID;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{page_title}'] = $post_data->post_title;
				return $replacements;
			}, 10, 1 );

		add_filter( 'nb_process_merge_tag_replacements',
			function ( $replacements ) use ( $post_data ) {
				$replacements['{page_name}'] = $post_data->post_name;
				return $replacements;
			}, 10, 1
		);

	}

	public function triggered( $post_id, $post_data, $update ) {

		if ( ! wp_is_post_revision($post_id) == false || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post_data->post_type != 'page' || $post_data->post_status == 'auto-draft' || $post_data->post_status == 'trash') {
			return;
		}

		add_filter( 'nb_queue_allowed_' . $this->id,
			function ( $allowed, $details ) use ( $post_data ) {
				$optional = $details->options->trigger->optional;

				if ( isset( $optional->page ) ) {
					if ( ! in_array( $post_data->post_title, $optional->page ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}, 10, 2
		);

		$this->merge_tag_filters( $post_data );

		if ( get_post_meta( $post_id, 'nb_new_post' ) ) {
			Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( 'page-new' ) );

			do_action( 'queued_page-new' );
			delete_post_meta( $post_id, 'nb_new_post' );
		} else {
			Queue::get_instance()->add_to_queue( Notifications::get_instance()->get_all_by_trigger( $this->id ) );
			do_action( 'queued_' . $this->id );
		}
	}

}

Triggers::register( new Page_Update() );