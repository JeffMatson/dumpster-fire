<?php
/**
 * Handles core functionality.
 *
 * @package JM_GF_File_Linker
 */

namespace JM_GF_Entry_Linker;

\GFForms::include_addon_framework();

/**
 * Core add-on class.
 */
class Core extends \GFAddOn {

	/**
	 * Defines the add-on version.
	 *
	 * @var string
	 */
	protected $_version = '1.0';

	/**
	 * Minimum Gravity Forms version required.
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.0';

	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	protected $_slug = 'jm-gf-entry-linker';

	/**
	 * The plugin path. Relative to the plugins directory.
	 *
	 * @var string
	 */
	protected $_path = 'jm-gf-entry-linker/jm-gf-entry-linker.php';

	/**
	 * The full path to the add-on file.
	 *
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * The add-on title.
	 *
	 * @var string
	 */
	protected $_title = 'Gravity Forms Entry Linker';

	/**
	 * The short title.
	 *
	 * @var string
	 */
	protected $_short_title = 'Entry Linker';

	/**
	 * Stores an instance of this class.
	 *
	 * @var \JM_GF_Entry_Linker\Core
	 */
	private static $_instance = null;

	/**
	 * Gets an instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return \JM_GF_Entry_Linker\Core
	 */
	public static function get_instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new \JM_GF_Entry_Linker\Core();
		}

		return self::$_instance;
	}

	/**
	 * Runs on WordPress init.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		add_action( 'gform_field_standard_settings', array( $this, 'select_linked_field' ), 10, 2 );
	}

	/**
	 * Runs on WordPress admin.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function init_admin() {
		parent::init_admin();
		add_action( 'gform_editor_js', array( $this, 'editor_js' ) );
	}

	/**
	 * Runs on the frontend.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function init_frontend() {
		parent::init_frontend();
		add_filter( 'gform_pre_render', array( $this, 'populate_linked_field' ) );
	}

	/**
	 * Adds JS to the field editor.
	 *
	 * Currently populates linked field selects on the form editor.
	 */
	public function editor_js() {
		?>
		<script type="text/javascript">
			for ( field_type in fieldSettings ) {
				field_type += ', .linked_field_setting';
			}
			jQuery( document ).bind( 'gform_load_field_settings', function( event, field, form ) {
				jQuery( '#linked_field_select' ).val( field.linkedField );
			});
		</script>
		<?php
	}

	public function get_linked_input_id( $input, $form_id, $linked_field ) {
		$regex = '/\d+(?:(?=\.))/';
		return preg_replace( $regex, $linked_field, $input['id'] );
	}

	/**
	 * Processes an input.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array  $input        The input from the Field Object.
	 * @param int    $form_id      The current form ID.
	 * @param string $linked_field The linked field.
	 *
	 * @return The modified input.
	 */
	public function maybe_process_input( $input, $form_id, $linked_field ) {

		$regex = '/\d+(?:(?=\.))/';
		$linked_field_id = preg_replace( $regex, $linked_field, $input['id'] );
		$client_details = $this->get_client_details( $form_id );

		if ( is_array( $client_details ) && array_key_exists( $linked_field_id, $client_details ) ) {
			$defaultval = $client_details[ $linked_field_id ];

			if ( ! empty( $defaultval ) ) {
				$input['defaultValue'] = $defaultval;
			}
		}

		return $input;
	}

	/**
	 * Processes a linked field.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param \GF_Field $field   The Field Object.
	 * @param int       $form_id The form ID.
	 *
	 * @return \GF_Field The modified field object.
	 */
	public function maybe_process_linked_field( $field, $form_id ) {

		$form = \GFAPI::get_form( $form_id );
		$settings = $this->get_form_settings( $form );
		$client_details = $this->get_client_details( $form_id );

		$this->client_details = $client_details;


		if ( ! is_array( $client_details ) ) {
			return $field;
		}

		$field_type = $field->type;

		if ( $field->linkedField ) {
			$linked_field = $field->linkedField;
		} elseif ( array_key_exists( 'form_type', $settings ) && $settings['form_type'] == 'patient_entry' ) {
			$linked_field = $field->id;
		} else {
			return $field;
		}

		if ( $field->type == 'list' ) {
			$field->inputName = $field->id;

		}

		if ( $field->type == 'time' ) {
				$field_id = $field->id;

				if ( ! empty( $client_details[ intval( $linked_field ) ] ) ) {
					$hour_minute = explode( ':', $client_details[ intval( $linked_field ) ] );

					$minute_am_pm = explode( ' ', $hour_minute[1] );

					$hour = $hour_minute[0];
					$minute = $minute_am_pm[0];
					$am_pm = strtoupper( $minute_am_pm[1] );

					$field->inputs[0]['defaultValue'] = $hour;
					$field->inputs[1]['defaultValue'] = $minute;
					$field->inputs[2]['defaultValue'] = $am_pm;
				}
		}

		if ( $field->type == 'radio' || $field->type == 'select' ) {
			foreach ( $field->choices as $key => $choice ) {
				$linked_input = intval( $field->linkedField );
				if ( $client_details[ $linked_input ] == $choice['value'] ) {
					$field->choices[ $key ]['isSelected'] = true;
				}
			}
		}

		if ( is_array( $field->inputs ) && $field->type !== 'checkbox' ) {
			foreach ( $field->inputs as $key => $input ) {
				$field->inputs[ $key ] = $this->maybe_process_input( $input, $form_id, $linked_field );
			}
		} elseif ( is_array( $field->choices ) ) {

			if ( $field->type == 'multiselect' ) {
				$inputs = json_decode( $client_details[ intval( $linked_field ) ] );
				foreach ( $field->choices as $key => $choice ) {
					if ( in_array( $choice['value'], $inputs ) ) {
						$field->choices[ $key ]['isSelected'] = true;
					}
				}
			} elseif ( $field->type == 'checkbox' ) {
				foreach ( $field->inputs as $key => $choice ) {
					$linked_input = $this->get_linked_input_id( $choice, $form_id, $linked_field );
					if ( is_array( $choice ) && array_key_exists( $linked_input, $client_details ) && ! empty( $client_details[ $linked_input ] ) ) {
						$field->choices[ $key ]['isSelected'] = true;
					}
				}
			} elseif ( $choice['value'] == $client_details[ $linked_field ] ) {
				$field->choices[ $key ]['isSelected'] = true;
			}

		} else {
			$field->defaultValue = $client_details[ $linked_field ];
		}

		return $field;
	}

	public function populate_linked_field( $form ) {
		$settings       = $this->get_form_settings( $form );
		$client_details = $this->get_client_details( $field->formId );
		$this->client_details = $client_details;

		foreach ( $form['fields'] as $key => $field ) {
			$form['fields'][ $key ]->allowsPrepopulate = true;
			if ( property_exists( $field, 'linkedField' ) && $field->type == 'list' ) {
				add_filter( 'gform_field_value', array( $this, 'populate_value' ), 10, 3 );
			} else {
				$form['fields'][ $key ] = $this->maybe_process_linked_field( $field, $field->formId );
			}
		}

		return $form;
	}

	public function populate_value( $value, $field, $name ) {
		if ( $field->type == 'list' ) {
			return unserialize( $this->client_details[ intval( $field->linkedField ) ] );
		}
		return $value;
	}

	public function get_client_details( $form_id ) {
		$client_id = rgget( 'client_id' );
		if ( empty( 'client_id' ) ) {
			return false;
		}

		\GFAPI::get_form( $form_id );
		$settings = $this->get_form_settings( $form );

		if ( $settings['form_type'] == 'patient_entry' ) {
			$linked_id = intval( $form_id );
		} else {
			$linked_id = intval( $this->get_linked_form_id( $form_id ) );
		}

		$filter = array(
			'field_filters' => array(
				array(
					'value' => $client_id,
				),
			),
		);

		$details = reset( \GFAPI::get_entries( $linked_id, $filter ) );
		return $details;
	}

	public function form_settings_fields( $form ) {
		return array(
			array(
				'title'  => 'Entry Linker Settings',
				'fields' => array(
					array(
						'label'   => 'Form Type',
						'type'    => 'select',
						'name'    => 'form_type',
						'tooltip' => 'Select a form to link values from.',
						'choices' => array(
							array(
								'label' => 'Patient Entry',
								'value' => 'patient_entry',
							),
							array(
								'label' => 'Linked Form',
								'value' => 'linked_form',
							),
						),
					),
					array(
						'label'   => 'Linked Form',
						'type'    => 'select',
						'name'    => 'linked_form',
						'tooltip' => 'Select a form to link values from.',
						'choices' => $this->get_form_list(),
						'dependency' => array( 'field' => 'form_type', 'values' => array( 'linked_form' ) ),
					),
				),
			),
		);
	}

	public function get_linked_form_id( $form_id ) {
		$form = \GFAPI::get_form( $form_id );
		$settings = $this->get_form_settings( $form );

		if ( is_array( $settings ) && array_key_exists( 'linked_form', $settings ) ) {
			return $settings['linked_form'];
		}

		return false;
	}

	public function get_linked_form( $form_id ) {
		$linked_form_id = $this->get_linked_form_id( $form_id );

		if ( $linked_form_id ) {
			$linked_form = \GFAPI::get_form( $linked_form_id );
			$this->linked_form = $linked_form;

			return $linked_form;
		}

		return false;
	}

	public function get_linked_form_fields() {
		$form = $this->linked_form;
		return $form['fields'];
	}

	public function get_linked_form_field_select() {
		$fields = $this->get_linked_form_fields();

		$output  = '<select id="linked_field_select" onchange="SetFieldProperty( \'linkedField\', this.value);">';
		$output .= '<option value="">Select a Linked Field</option>';
		foreach ( $fields as $field ) {
			$output .= '<option value="' . $field->id . '">' . $field->label . '</option>';
		}
		$output .= '</select>';
		return $output;
	}

	public function select_linked_field( $position, $form_id ) {
		$form = \GFAPI::get_form( $form_id );
		$settings = $this->get_form_settings( $form );

		if ( is_array( $settings ) && array_key_exists( 'form_type', $settings ) && $settings['form_type'] == 'linked_form' ) {
			if ( $position === 25 ) :
				$linked_form = $this->get_linked_form( $form_id );
				if ( ! $linked_form ) {
					return false;
				}
		?>

		<li class="linked_field_setting">
			<label for="field_admin_label">
				<?php esc_html_e( 'Linked Field', 'gravityforms' ); ?>
				<?php gform_tooltip( 'form_field_linked_field' ); ?>
			</label>
			<?php echo $this->get_linked_form_field_select(); ?>
		</li>

		<?php
			endif;
		}


	}

	public function get_form_list() {
		$forms = \GFAPI::get_forms();

		$return  = array();
		foreach ( $forms as $form ) {
			$return[] = array(
				'label' => esc_html( $form['title'] ),
				'value' => $form['id'],
			);
		}

		return $return;
	}
}
