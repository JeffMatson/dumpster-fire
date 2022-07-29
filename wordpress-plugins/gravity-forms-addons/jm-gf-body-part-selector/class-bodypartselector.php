<?php

GFForms::include_addon_framework();

class BodyPartSelector extends GFAddOn {

	protected $_version = JM_GF_BODY_PART_SELECTOR_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'bodypartselector';
	protected $_path = 'jm-gf-body-part-selector/jm-gf-body-part-selector.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Body Part Selector Field';
	protected $_short_title = 'Body Part Selector';

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
			require_once( 'includes/class-body-part-selector-field.php' );
		}
	}

	public function init_admin() {
		parent::init_admin();

		add_action( 'gform_field_standard_settings', array( $this, 'field_appearance_settings' ), 10, 2 );
		add_action( 'gform_editor_js', array( $this, 'editor_script' ) );
	}

	public function adjust_image_display() {

	}

	function editor_script(){
		?>
		<script type='text/javascript'>
			fieldSettings.body_part_selector += ', .body_selector_image_url_setting';

			jQuery(document).bind('gform_load_field_settings', function(event, field, form){
				jQuery('#body_selector_image_url').val( field.image_url );
				jQuery('#body_selector_image_height').val( field.image_height );
				jQuery('#body_selector_image_width').val( field.image_width );
			});
		</script>
		<?php
	}

	public function field_appearance_settings( $position, $form_id ) {
		if ( $position === 25 ) {
			?>
			<li class="body_selector_image_url_setting field_setting">
				<label for="field_admin_label">
					<?php esc_html_e( 'Image URL', 'simplefieldaddon' ); ?>
				</label>
				<input id="body_selector_image_url" type="text" class="fieldwidth-1" onkeyup="SetFieldProperty('image_url', jQuery(this).val());" onchange="SetFieldProperty('image_url', jQuery(this).val());"/>
				<label for="field_admin_label">
					<?php esc_html_e( 'Image Height', 'simplefieldaddon' ); ?>
				</label>
				<input id="body_selector_image_height" type="text" class="fieldwidth-1" onkeyup="SetFieldProperty('image_height', jQuery(this).val());" onchange="SetFieldProperty('image_height', jQuery(this).val());"/>
				<label for="field_admin_label">
					<?php esc_html_e( 'Image Width', 'simplefieldaddon' ); ?>
				</label>
				<input id="body_selector_image_width" type="text" class="fieldwidth-1" onkeyup="SetFieldProperty('image_width', jQuery(this).val());" onchange="SetFieldProperty('image_width', jQuery(this).val());"/>
			</li>

			<?php
		}
	}

	public function scripts() {
		$scripts = array(
			array(
				'handle'    => 'jcanvas',
				'src'       => $this->get_base_url() . '/js/jcanvas.js',
				'version'   => $this->_version,
				'deps'      => array( 'jquery' ),
				'in_footer' => false,
				'enqueue'   => array(
					array( 'field_types' => array( 'body_part_selector' ) )
				)
			),
			array(
				'handle'    => 'jm_gf_body_part_selector_field_frontend_js',
				'src'       => $this->get_base_url() . '/js/frontend.js',
				'version'   => $this->_version,
				'deps'      => array( 'jquery', 'jcanvas' ),
				'in_footer' => false,
				'enqueue'   => array(
					array( 'field_types' => array( 'body_part_selector' ) )
				)
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	public function styles() {
		$styles = array(
			array(
				'handle'  => 'jm_gf_body_part_selector_field_frontend_css',
				'src'     => $this->get_base_url() . '/css/frontend.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'body_part_selector' ) )
				)
			)
		);

		$styles = array_merge( parent::styles(), $styles );

		return $styles;
	}

}
