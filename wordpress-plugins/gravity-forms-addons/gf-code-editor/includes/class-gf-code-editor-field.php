<?php

class GF_Code_Editor_Field extends GF_Field {

	public $type = 'code-editor';

	public function get_form_editor_field_title() {
		return esc_attr__( 'Code Editor', 'gf-code-editor' );
	}

	function get_form_editor_field_settings() {
		return array(
			'post_custom_field_setting',
			'conditional_logic_field_setting',
			'prepopulate_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'size_setting',
			'rules_setting',
			'visibility_setting',
			'duplicate_setting',
			'description_setting',
			'css_class_setting',
		);
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	public function get_field_input( $form, $value = '', $entry = null ) {

		$form_id         = $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$id              = (int) $this->id;

		$input = "<style type=\"text/css\" media=\"screen\">" .
		         	"#gf_code_editor_input_{$id} { " .
		         		"width: 100%;" .
		         		"height: 300px;" .
		         	"}" .
		         "</style>" .
				 "<div class='ginput_container' id='gf_code_editor_container_{$id}'>" .
				 	"<div id='gf_code_editor_input_{$id}'></div>" .
				 	"<input type=\"hidden\" name=\"input_{$id}\" id=\"input_{$id}\" value=\"{$value}\"/>" .
				 "</div>";

		return $input;
	}

	public function get_form_inline_script_on_page_render( $form ) {
		$id = $this->id;

		$script = "var textarea = jQuery('input[name=\"input_{$id}\"]'); ";
		$script .= "var editor = ace.edit('gf_code_editor_input_{$id}');";
		$script .= "editor.setTheme('ace/theme/monokai');";
		$script .= "editor.getSession().setMode({ path: 'ace/mode/php', inline: true});";
		$script .= "editor.getSession().setUseWrapMode(true);";
		$script .= "editor.getSession().setValue(textarea.val());";
		$script .= "editor.getSession().on('change', function () {";
		$script .= 		"textarea.val(editor.getSession().getValue());";
		$script .= "});";

		return $script;
	}

	public function validate( $value, $form ) {
		$this->failed_validation = false;
	}
}

GF_Fields::register( new GF_Code_Editor_Field() );
