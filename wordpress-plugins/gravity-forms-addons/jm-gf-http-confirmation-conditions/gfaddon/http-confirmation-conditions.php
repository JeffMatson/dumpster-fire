<?php
/**
 * Contains the HTTP_Confirmation_Conditions class.
 *
 * @package JM_GF_HTTP_Confirmation_Conditions\GFAddOn
 * @author  Jeff Matson <jeff@jeffmatson.net>
 */

namespace JM_GF_HTTP_Confirmation_Conditions\GFAddOn;

\GFForms::include_feed_addon_framework();

class HTTP_Confirmation_Conditions extends \GFFeedAddOn {

	protected $_version = '0.1';
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'jm-gf-http-confirmation-conditions';
	protected $_path = 'jm-gf-http-confirmation-conditions/jm-gf-http-confirmation-conditions.php';
	protected $_full_path = HTTP_CONFIRMATION_CONDITIONS_FILE_PATH;
	protected $_title = 'GF HTTP Confirmation Conditions';
	protected $_short_title = 'HTTP Confirmation Conditions';

	public $_async_feed_processing = true;

	/**
	 * Holds an instance of this class.
	 *
	 * @var object|null $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function feed_settings_fields() {
		return array(
			array(
				'title' => 'HTTP Request Settings',
				'fields' => array(
					array(
						'name'                => 'customerDetails',
						'label'               => esc_html__( 'Customer Details', 'sometextdomain' ),
						'type'                => 'field_map',
						'limit'               => 20,
						'exclude_field_types' => 'creditcard',
						'field_map'           => $this->standard_fields_for_feed_mapping(),
						'tooltip'             => '<h6>' . esc_html__( 'Customer Email', 'sometextdomain' ) . '</h6>' . esc_html__( 'You may send custom meta information to [...]. A maximum of 20 custom keys may be sent. The key name must be 40 characters or less, and the mapped data will be truncated to 500 characters per requirements by [...]. ', 'sometextdomain' ),
					),
					array(
						'label'             => 'Username',
						'type'              => 'text',
						'name'              => 'ApiUsername',
						'tooltip'           => 'This is the tooltip',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'label'             => 'Password',
						'type'              => 'text',
						'name'              => 'ApiPassword',
						'tooltip'           => 'This is the tooltip',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'label'             => 'Store Alias',
						'type'              => 'text',
						'name'              => 'storeAlias',
						'tooltip'           => 'This is the tooltip',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
				),
			),
		);
	}

	public function standard_fields_for_feed_mapping() {
		return array(
			array(
				'name'          => 'email',
				'label'         => esc_html__( 'Email Address', 'sometextdomain' ),
				'required'      => true,
				'field_type'    => array( 'email', 'hidden' ),
				'default_value' => $this->get_first_field_by_type( 'email' ),
			),
		);
	}

	public function supported_notification_events( $form ) {

		return array(
			'customer_email_exists' => 'Customer Email Exists',
			'new_customer_email'    => 'New Customer Email',
		);
	}

	public function process_feed( $feed, $entry, $form ) {

		$customer_details = $this->get_field_map_fields( $feed, 'customerDetails' );

		$request_params = array(
			'username'   => $feed['meta']['ApiUsername'],
			'password'   => $feed['meta']['ApiPassword'],
			'storealias' => $feed['meta']['storeAlias'],
		);

		foreach ( $customer_details as $name => $field_id ) {
			$request_params[ $name ] = $this->get_field_value( $form, $entry, $field_id );
		}

		$api_response = new \JM_GF_HTTP_Confirmation_Conditions\Request_Controller( $request_params );

		if ( $api_response->customer_exists() ) {
			$notification_action = 'customer_email_exists';
		} else {
			$notification_action = 'new_customer_email';
		}

		\GFAPI::send_notifications( $form, $entry, $notification_action );

		return;
	}

}
