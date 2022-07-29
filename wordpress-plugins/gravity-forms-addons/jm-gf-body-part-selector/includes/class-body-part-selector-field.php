<?php
/**
 * Contains the field.
 *
 * @package JM_GF_Body_Part_Selector\Includes
 */

/**
 * Undocumented class
 */
class Body_Part_Selector_Field extends GF_Field {

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public $type = 'body_part_selector';

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Body Part Selector', 'simplefieldaddon' );
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'rules_setting',
			'visibility_setting',
			'duplicate_setting',
			'description_setting',
			'css_class_setting',
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $form
	 * @param string $value
	 * @param [type] $entry
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor = $this->is_form_editor();

		if ( $is_entry_detail ) {
			return '<img src="' . $value . '">';
		}

		if ( $is_form_editor ) {
			return '';
		}

		$input  = '<canvas id="body_canvas_' . $this->id . '" height="100" width="100" data-id="' . $this->id . ' data-image=' . $this->image_url . '"></canvas>';
		$input .= '<input type="hidden" class="body_image_markup body_image_markup_' . $this->id . '" name="input_' . $this->id . '"></input>';
		$input .= '<script>bodyPartSelector.init(' . $this->id . ', \'' . $this->image_url .'\', ' . $this->image_height .', ' . $this->image_width . ');</script>';

		return $input;
	}

	/**
	 * Undocumented function
	 *
	 * @param string  $value
	 * @param string  $currency
	 * @param boolean $use_text
	 * @param string  $format
	 * @param string  $media
	 *
	 * @return string
	 */
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		return '<img src="' . $value . '">';
	}

	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	public function allow_html(){
		return true;
	}
}

GF_Fields::register( new Body_Part_Selector_Field() );
