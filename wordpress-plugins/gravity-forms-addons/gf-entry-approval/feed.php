<?php
/**
 * Contains the GF_Entry_Approval\Feed class.
 *
 * @package GF_Entry_Approval
 */

namespace GF_Entry_Approval;

// Include the addon framework.
\GFForms::include_addon_framework();

/**
 * Main processing of the add-on.
 *
 * @package GF_Entry_Approval
 * @see GFAddOn
 */
class Feed extends \GFAddOn {

	/**
	 * Contains the version string.
	 *
	 * @var string
	 */
	protected $_version                  = GF_ENTRY_APPROVAL_VERSION;

	/**
	 * Minimum Gravity Forms version required.
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '1.9';

	/**
	 * Add-on slug.
	 *
	 * @var string
	 */
	protected $_slug                     = 'gf_entry_approval';

	/**
	 * Path to the main plugin.
	 *
	 * @var string
	 */
	protected $_path                     = GF_ENTRY_APPROVAL_PATH;

	/**
	 * Path to this file.
	 *
	 * @var string
	 */
	protected $_full_path                = __FILE__;

	/**
	 * Title of this add-on.
	 *
	 * @var string
	 */
	protected $_title                    = 'Gravity Forms Entry Approval';

	/**
	 * The short title.
	 *
	 * @var string
	 */
	protected $_short_title              = 'Entry Approval';

	/**
	 * Contains an instance of this class.
	 *
	 * @var Feed|null
	 */
	private static $_instance            = null;

	/**
	 * Gets an instance of this class.
	 *
	 * @return Feed
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new Feed;
		}

		return self::$_instance;
	}

	/**
	 * Initializes the add-on.
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		add_action( 'gform_after_submission', array( $this, 'after_submission' ), 10, 2 );
	}

	/**
	 * Sets the form settings fields.
	 *
	 * @param array $form The Form Object.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		return array(
			array(
				'title'  => 'Entry Approval Settings',
				'fields' => array(
					array(
						'label'   => 'Enable Entry Approval',
						'type'    => 'checkbox',
						'name'    => 'entry_approval_enabled',
						'choices' => array(
							array(
								'label' => 'Enabled',
								'name'  => 'entry_approval_enabled',
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Handles functionality to be performed after the form is submitted.
	 *
	 * @param array $entry The Entry Object.
	 * @param array $form  The Form Object.
	 *
	 * @return void
	 */
	public function after_submission( $entry, $form ) {

		$form_settings = $this->get_form_settings( $form );

		if ( array_key_exists( 'entry_approval_enabled', $form_settings ) && $form_settings['entry_approval_enabled'] == 1 ) {
			$approval_key = uniqid( 'approval_', true );
			gform_update_meta( $entry['id'], 'approval_key', $approval_key );

			if ( ! rgar( $entry, 'approval_status' ) ) {
				$approval_status = 'pending';
				gform_update_meta( $entry['id'], 'approval_status', $approval_status );

				\GFAPI::send_notifications( $form, $entry, 'entry_pending' );
			}
		}
	}

	/**
	 * Handles our additional entry meta keys.
	 *
	 * @param array $entry_meta The current entry meta.
	 * @param int   $form_id    The form ID.
	 *
	 * @return array
	 */
	public function get_entry_meta( $entry_meta, $form_id ) {
		$entry_meta['approval_key'] = array(
			'label'             => 'Approval Key',
			'is_numeric'        => false,
			'is_default_column' => false,
			'filter'            => array(
				'operators' => array(
					'is',
					'isnot',
					'>',
					'<',
				),
			),
		);

		$entry_meta['approval_status'] = array(
			'label'             => 'Approval Status',
			'is_numeric'        => false,
			'is_default_column' => false,
			'filter'            => array(
				'operators' => array(
					'is',
					'isnot',
					'>',
					'<',
				),
			),
		);

		return $entry_meta;
	}
}
