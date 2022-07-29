<?php
/**
 * Contains the main add-on functionality.
 *
 * @package jm-gf-submit-on-select
 */

GFForms::include_addon_framework();

/**
 * The main GF_Submit_On_Select class.
 */
class GF_Submit_On_Select extends GFAddOn {

	/**
	 * The current version of the add-on.
	 *
	 * @var string
	 */
	protected $_version = JM_GF_SUBMIT_ON_SELECT_VERSION;

	/**
	 * The minimum Gravity Forms version required.
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.0.0';

	/**
	 * The add-on slug.
	 *
	 * @var string
	 */
	protected $_slug = 'gf-submit-on-select';

	/**
	 * The path the the main plugin file.
	 *
	 * @var string
	 */
	protected $_path = 'jm-gf-submit-on-select/jm-gf-submit-on-select.php';

	/**
	 * The full path to this file.
	 *
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * The add-on title.
	 *
	 * @var string
	 */
	protected $_title = 'Gravity Forms Submit On Select';

	/**
	 * The short add-on title.
	 *
	 * @var string
	 */
	protected $_short_title = 'Submit On Select';

	/**
	 * Holds an instance of this class.
	 *
	 * @var null|GF_Submit_On_Select
	 */
	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GF_Submit_On_Select
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GF_Submit_On_Select();
		}
		return self::$_instance;
	}

	/**
	 * Actions to run on init.
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_pre_render', array( $this, 'register_click_handlers' ) );
	}

	/**
	 * Creates the form settings fields.
	 *
	 * @param array $form The Form Object.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		return array(
            array(
                'title'  => esc_html__( 'Simple Form Settings', 'jm-gf-submit-on-select' ),
                'fields' => array(
                    array(
                        'label'   => esc_html__( 'Enabled', 'jm-gf-submit-on-select' ),
                        'type'    => 'checkbox',
                        'name'    => 'enabled',
                        'tooltip' => esc_html__( 'Enable Submit on Select for this form.', 'jm-gf-submit-on-select' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'Enabled', 'jm-gf-submit-on-select' ),
                                'name'  => 'enabled',
                            ),
                        ),
					),
					array(
						'name'      => 'submission_choices',
						'label'     => esc_html__( 'Submission Field Mapping', 'jm-gf-submit-on-select' ),
						'type'      => 'field_map',
						'field_map' => array(
							array(
								'name'          => 'submission_field',
								'label'         => esc_html__( 'Submission Field', 'jm-gf-submit-on-select' ),
								'required'      => false,
								'field_type'    => array( 'radio' ),
								'default_value' => 'Select a field',
							),
						),
						'tooltip'   => '<h6>' . esc_html__( 'Map Fields', 'jm-gf-submit-on-select' ) . '</h6>' . esc_html__( 'Select the field that will automatically submit the form when selected.', 'jm-gf-submit-on-select' )
					),
                ),
            ),
        );
	}

	/**
	 * Validation placeholder for field mapping.
	 *
	 * @param array $field The Field Object.
	 *
	 * @return true
	 */
	public function validate_mapped_field( $field ) {
		return true;
	}

	/**
	 * Checks if the field is defined.
	 *
	 * @param array $form_settings The form settings.
	 *
	 * @return bool
	 */
	public function has_choices_setting( $form_settings ) {
		if ( isset( $form_settings['submission_choices_submission_field'] ) && ! empty( $form_settings['submission_choices_submission_field'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if Submit on Select is enabled on the form.
	 *
	 * @param array $form_settings The form settings.
	 *
	 * @return bool
	 */
	public function is_enabled( $form_settings ) {
		if ( isset( $form_settings['enabled'] ) && $form_settings['enabled'] === '1' ) {
			return true;
		}

		return false;
	}

	/**
	 * Outputs scripts to submit the form on field selection.
	 *
	 * @param string $submission_field The submission field ID.
	 * @param int    $form_id The form ID.
	 *
	 * @return void
	 */
	public function output_submit_script( $submission_field, $form_id ) {
		?>
		<script>
			jQuery(document).ready(function(){
				jQuery('input[name="input_<?php esc_html_e( $submission_field ); ?>"]').click(function() {
					jQuery('form#gform_<?php esc_html_e( $form_id ); ?>').submit();
				})
			});
		</script>
		<?php
	}

	/**
	 * Registers the click handler JS.
	 *
	 * @param array $form The Form Object.
	 *
	 * @return array
	 */
	public function register_click_handlers( $form ) {
		$form_settings = $this->get_form_settings( $form );

		if ( $this->is_enabled( $form_settings ) ) {
			if ( $this->has_choices_setting( $form_settings ) ) {
				$this->output_submit_script( $form_settings['submission_choices_submission_field'], $form['id'] );
			}
		}

		return $form;
	}
}
