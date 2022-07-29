<?php

GFForms::include_addon_framework();

class GF_Code_Editor extends GFAddOn {
	protected $_version = GF_CODE_EDITOR_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gf-code-editor';
	protected $_path = 'gf-code-editor/gf-code-editor.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Code Editor Add-On';
	protected $_short_title = 'Code Editor Add-On';

	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-gf-code-editor-field.php' );
		}
	}

	public function init_admin() {
		parent::init_admin();

		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_field_appearance_settings', array( $this, 'field_appearance_settings' ), 10, 2 );
	}

	public function init_frontend() {
		parent::init_frontend();
		add_action( 'gform_after_submission', array( $this, 'handle_snippet_meta' ), 10, 2 );
	}

	public function handle_snippet_meta( $entry, $form ) {
		$post_id = $entry['post_id'];
		update_post_meta( $post_id, 'snippet_content', rgar( $entry, '1' ) );
	}

	public function tooltips( $tooltips ) {
		$simple_tooltips = array(
			'input_class_setting' => sprintf( '<h6>%s</h6>%s', esc_html__( 'Input CSS Classes', 'simplefieldaddon' ), esc_html__( 'The CSS Class names to be added to the field input.', 'simplefieldaddon' ) ),
		);

		return array_merge( $tooltips, $simple_tooltips );
	}

	public function field_appearance_settings( $position, $form_id ) {
		// Add our custom setting just before the 'Custom CSS Class' setting.
		if ( $position == 250 ) {
			?>
			<li class="input_class_setting field_setting">
				<label for="input_class_setting">
					<?php esc_html_e( 'Input CSS Classes', 'simplefieldaddon' ); ?>
					<?php gform_tooltip( 'input_class_setting' ) ?>
				</label>
				<input id="input_class_setting" type="text" class="fieldwidth-1" onkeyup="SetInputClassSetting(jQuery(this).val());" onchange="SetInputClassSetting(jQuery(this).val());"/>
			</li>

			<?php
		}
	}

	public function scripts() {
		$scripts = array(
			array(
				'handle'    => 'ace',
				'src'       => $this->get_base_url() . '/js/vendor/ace/ace.js',
				'version'   => '17.10.17',
				'deps'      => array(),
				'in_footer' => false,
				'enqueue'   => array(
					array(
						'field_types' => array( 'code-editor' ),

					),
					array(
						'admin_page' => array( 'form_editor' ),
					),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}
}
