<?php

namespace GF_Post_Content_Fields\Fields;

class GF_Field_Post_Editor extends \GF_Field {

	public $type = 'post-editor';

	public function get_form_editor_field_title() {
		return esc_attr__( 'Post Editor', 'gravityforms' );
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'standard_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	public function get_value_entry_detail( $value = '', $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

		$value = json_decode( $value );
		$value = json_decode( $value );

		$data = '';
		foreach ( $value as $item ) {
			$data .= $item->content;
		}

		return $data;
	}

	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
		return 'See entry details.';
	}

	public function get_value_save_entry( $value, $form, $input_name, $lead_id, $lead ) {
		return json_encode( $value );
	}

	public function is_continued() {
		return isset( $_GET['gf_token'] );
	}

	public function set_save_continue() {
		if ( $this->is_continued() ) {
			$save_continue = \GFFormsModel::get_incomplete_submission_values( $_GET['gf_token'] );
			$submission = json_decode( $save_continue['submission'] )->submitted_values;

			if ( property_exists( $submission, $this->id ) ) {
				$id = $this->id;
				if ( ! empty( $submission->$id ) ) {
					return json_decode( $submission->$id );
				}

			}
		}
		return '{}';
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		if ( ! is_admin() ) {
			$return  = '<script>';
			$return .= 'if ( window.gfpcf == undefined || window.gfpcf == null ) { window.gfpcf = [];  }';

			$gfpcf_obj = array(
				'id'        => $this->id,
				'post_type' => 'post',
			);

			if ( property_exists( $this, 'postType' ) ) {
				$gfpcf_obj['post_type'] = $this->postType;
			}

			$gfpcf_obj['posts'] = \GF_Post_Content_Fields\Core::build_existing_posts( $gfpcf_obj['post_type'] );
			$gfpcf_obj['existing'] = $this->set_save_continue();

			$return .= 'window.gfpcf.push(' . json_encode( $gfpcf_obj ) . ');';
			$return .= '</script>';
			$return .= '<div id="gf-post-content-editor-' . $this->id . '" :gfpcf_input_id="' . $this->id . '" class="ginput_container"></div>';
		} else {
			$return = '';
		}

		return $return;
	}

	public function get_entry_inputs() {
		return null;
	}

	public function allow_html() {
		return true;
	}

	public function get_form_editor_field_settings() {
		$settings = array(
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'duplicate_setting',
			'description_setting',
			'css_class_setting',
		);
		return apply_filters( 'gfpcf_settings', $settings );
	}

}
