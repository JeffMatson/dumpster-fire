<?php

namespace NotifyBot\Views;

use NotifyBot\Core;
use NotifyBot\Global_Merge_Tags;
use NotifyBot\Models\Global_Settings;
use NotifyBot\Models\Notifications;
use NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Methods;
use NotifyBot\Notifications\Services;
use NotifyBot\Notifications\Triggers;

// If not accessed from the WordPress admin, or not logged in, kill it
if ( ! ABSPATH || ! is_admin() ) {
	die();
}

// If the group isn't set, redirect to the next group ID
if ( ! isset( $_GET['group_id'] ) && isset( $_GET['page'] ) && $_GET['page'] == 'notifybot-add-new' ) {
	$group_id = Notifications::get_instance()->next_id();
	header('Location:' . $_SERVER['REQUEST_URI'] . '&group_id=' . $group_id );
}

/**
 * Handles the display of the Add New page
 * @package NotifyBot\Views
 */
class Add_New extends View_Controller {

	/**
	 * The header that should be displayed for each section
	 * @var string
	 */
	public $event_header = 'Events';
	public $trigger_header = 'Triggers';
	public $methods_header = 'Notification Methods';
	public $services_header = 'Services';

	private static $existing = false;
	private static $group_data = false;

	/**
	 * Returns an instance of this class, if available
	 * @since 2016.04.10.0
	 * @access public
	 * @var object $_instance This class object
	 */
	private static $instance = null;

	/**
	 * Gets an instance of this class, if it doesn't already exist
	 *
	 * @since 2016.04.10.0
	 * @access public
	 * @static
	 *
	 * @return object An instance of this class
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return static::$instance;
	}

	/**
	 * Adds any actions required by the Add New page
	 *
	 * @since 2016.04.10.0
	 * @access public
	 */
	public function add_actions() {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'notifybot-add-new' ) { return; }

		add_action( 'admin_print_footer_scripts', array( $this, 'run_quicktags' ) );
		add_action( 'admin_init', array( $this, 'process_form_submit' ) );
	}

	/**
	 * Enqueues any scripts required by the Add New page
	 *
	 * @since 2016.04.10.0
	 * @access public
	 */
	public function enqueue_scripts() {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'notifybot-add-new' ) { return; }

		$core = Core::get_instance();
		wp_enqueue_script( 'nb-add-new', $core->url() . 'js/add-new.js', array( 'jquery', 'jquery-collapse', 'jquery-validate', 'select2' ) );
		wp_enqueue_style( 'nb-add-new', $core->url() . 'css/add-new.css', array( 'select2', 'notifybot' ) );
		
	}

	/**
	 * Gets and formats all data available for the notification group
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @return array $setting_data Multidimensional array containing all information on the group
	 */
	public function get_all_group_data() {

		$existing = self::$existing;
		if ( ! $existing )
			return false;

		if ( $existing && is_array( $existing ) )
			foreach ( $existing as $existing_item ) {
				$options = json_decode( $existing_item->options );
				$setting_data['service']['enabled'][]                              = $existing_item->service;
				$setting_data['trigger']['enabled'][]                              = $existing_item->event_trigger;
				$setting_data['service']['options'][$existing_item->service]       = $options->service;
				$setting_data['trigger']['options'][$existing_item->event_trigger] = $options->trigger;
			}

		if ( ! isset( $setting_data ) ) {
			return false;
		}

		$setting_data['service']['enabled'] = array_unique( $setting_data['service']['enabled'] );

		self::$group_data = $setting_data;

		return $setting_data;
	}

	/**
	 * Generates the notification group name details
	 *
	 * @since 2016.04.11.2
	 * @access public
	 * @return string The group name input field
	 */
	public function generate_title_field() {
		if ( self::$existing ) {
			$group_title = Global_Settings::get_instance()->get_value( 'nb_group_title_' . self::$existing[0]->group_id );
			return '<input type="text" class="nb-title" name="nb-title" value="' . esc_html( $group_title ) . '" required>';
		}

		return '<input type="text" class="nb-title" name="nb-title" value="Untitled" required>';
	}

	/**
	 * Generates item headers
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @param string $id               The ID of the item. Ex: email-local
	 * @param string $label            Label that will be displayed on the item. Ex: Local Email
	 * @param string $header_text_type The header text type. Defaults to h2
	 * @param array  $has_input If     there's an input in the header, contains values to generate it
	 */
	public function generate_header ( $id, $label, $header_text_type = 'h2', $has_input = array(), $required = '' ) {
		?>
		<div class="nb-header-collapse collapse-<?php esc_attr_e( $id, 'notifybot' ) ?>">
			<div id="collapse-<?php esc_attr_e( $id ) ?>" class="nb-header-container">
				<label>
				<?php if ( ! empty( $has_input ) ) : ?>
					<input id="<?php echo $id ?>" type="<?php echo $has_input['type'] ?>" class="<?php echo $has_input['class']; ?>" name="<?php print_r($has_input['name']); ?>" <?php echo $this->maybe_checked( $id, $has_input['check_against'] )?> value="<?php print_r($has_input['value']); ?>" <?php echo($required); ?>>
				<?php endif; ?>
				<<?php esc_attr_e($header_text_type); ?>><?php esc_attr_e( $label, 'notifybot' ) ?></<?php esc_attr_e($header_text_type); ?>>
				</label>

				<div class="dashicons dashicons-arrow-down nb-box-expand"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Checks if an option group has existing options that are set
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @param string $type     The type of option
	 * @param string $item     The option item
	 * @param string $required If this is a required option
	 *
	 * @return bool
	 */
	public function has_existing_options( $type, $item, $required ) {

		if ( ! self::$group_data ) {
			return false;
		}

		if ( array_key_exists( $item, self::$group_data[ $type ]['options'] ) && isset( self::$group_data[ $type ]['options'][ $item ]->$required ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Determines if a checkbox should be checked.
	 * Used in checking existing options
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @param string $current_item    The item to check for
	 * @param array $existing_options Contains any existing items to check through
	 *
	 * @return string
	 */
	public function maybe_checked( $current_item, $existing_options ) {
		if ( in_array( $current_item, $existing_options ) ) {
			return 'checked';
		} else {
			return '';
		}
	}

	/**
	 * Determines is a select item is selected
	 * Used in checking existing options
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @param string $current_select_item The item to check for
	 * @param array $existing_options     Array containing the existing options to check through
	 *
	 * @return string
	 */
	public function maybe_selected( $current_select_item, $existing_options ) {
		if ( in_array( $current_select_item, $existing_options ) ) {
			return 'selected';
		} else {
			return '';
		}
	}

	/**
	 * Displays a message if there are any required settings.
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @param object $current The service or trigger object
	 *
	 * @return string The message to be displayed
	 */
	public function has_global_requires( $current ) {
		if ( property_exists( $current, 'global_settings' ) && ! empty( $current->global_settings ) ) {
			if ( array_key_exists( 'required', $current->global_settings ) && ! empty( $current->global_settings['required'] ) ) {

				$url = add_query_arg( array(
					'page' => 'notifybot-settings',
				), admin_url( 'admin.php' ) );

				return '<p class="required-warning" style="color:red">Requires and uses additional <a style="color:red; font-weight: bold; text-decoration: underline;" href="' . esc_url( $url ) . '">global configuration settings</a></p>';
			}
		}

		return '';
	}

	/**
	 * Checks if the object has required options
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @param object $current The service or trigger object
	 *
	 * @return bool True if the object has required options.  False otherwise.
	 */
	public function has_requires( $current ) {
		if ( property_exists( $current, 'local_settings' ) && ! empty( $current->local_settings ) ) {
			foreach ( $current->local_settings as $setting ) {
				if ( array_key_exists( 'required', $setting ) && $setting['required'] === true ) {
					return true;
				}
			}
		}

		return false;
	}

	public function has_optional( $current ) {
		if ( property_exists( $current, 'local_settings' ) && ! empty( $current->local_settings ) ) {
			foreach ( $current->local_settings as $setting ) {
				if ( array_key_exists( 'required', $setting ) && $setting['required'] === false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Builds text inputs
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @param string $type         The section that the option belongs to
	 * @param object $current      The service or trigger object that options are for
	 * @param string $option_key   The name of the option being checked
	 * @param array  $option_value The option array being checked
	 * @param string $required     Returns required if it is a required option
	 */
	public function build_input_text( $type, $current, $option_key, $option_value, $required ) {

		if ( $type == 'service' ) {
			$id = $current->id;
			$name = 'nb[service][' . esc_attr( $id, 'notifybot' ) . '][' . $required . '][' . esc_attr( $option_key, 'notifybot' ) . ']';
		} elseif ( $type == 'trigger' ) {
			$event_id = $current['event']->id;
			$id = $current['trigger']->id;
			$name = 'nb[trigger][' . esc_attr( $event_id ) . '][' . esc_attr( $id, 'notifybot' ) . '][optional][' . esc_attr( $option_key, 'notifybot' ) . ']" title="optional-select';
		} else {
			return;
		}

		if ( $this->has_existing_options( $type, $id, $required ) ) {
			$existing_group_data = self::$group_data;
			$existing = $existing_group_data[ $type ]['options'][ $id ]->$required->$option_key;
			$value = 'value="' . $existing . '"';
		} elseif ( array_key_exists( 'default_value', $option_value ) ) {
			$value = 'value="' . $option_value['default_value'] . '"';
		} else {
			$value = '';
		}

		echo '<input type="' . $option_value['input_type'] . '" class=nb-' . esc_attr( $required ) . '-setting ' . esc_attr( $required ) . '-' . esc_attr( $id ) . ' name="' . esc_attr( $name ) . '" ' . $value . $required .'>' ;

	}

	/**
	 * Builds select inputs
	 *
	 * @since 2016.04.13.0
	 * @access public
	 *
	 * @param string $type         The section that the option belongs to
	 * @param object $current      The service or trigger object that options are for
	 * @param string $option_key   The name of the option being checked
	 * @param array  $option_value The option array being checked
	 * @param string $required     Returns required if it is a required option
	 */
	public function build_input_select( $type, $current, $option_key, $option_value, $required ) {

		if ( $type == 'service' ) {
			$id = $current->id;
			$name = 'nb[service][' . esc_attr( $id, 'notifybot' ) . '][' . $required . '][' . esc_attr( $option_key, 'notifybot' ) . '][]';
		} elseif ( $type == 'trigger' ) {
			$event_id = $current['event']->id;
			$id = $current['trigger']->id;
			$name = 'nb[trigger][' . esc_attr( $event_id ) . '][' . esc_attr( $id, 'notifybot' ) . '][optional][' . esc_attr( $option_key, 'notifybot' ) . '][]" title="optional-select';
		} else {
			return;
		}

		if ( $option_value['multiple'] === true ) {
			$multiple = 'multiple';
		} else {
			$multiple = '';
		}
		?>

		<select <?php esc_attr_e( $multiple ); ?>
		        class="nb-select2 nb-<?php esc_attr_e( $required ); ?>-setting <?php esc_attr_e( $required ); ?>-<?php esc_attr_e( $id ) ?>"
		        name="<?php echo $name ?>"
		        title="<?php esc_attr_e( $required ) ?>-select" data-placeholder="<?php echo $option_value['placeholder'] ?>">

		<?php

		if ( array_key_exists( 'selections', $option_value ) && $this->has_existing_options( $type, $id, $required ) ) {

				$existing_group_data = self::$group_data;
				$existing_options    = $existing_group_data[ $type ]['options'][ $id ]->$required->$option_key;
				$existing_custom     = array_diff( $existing_options, $option_value['selections'] );

				foreach ( $option_value['selections'] as $selection ) {
					echo '<option value="' . esc_attr( $selection ) . '" ' . $this->maybe_selected( $selection, $existing_options ) . '>' . esc_attr( $selection ) . '</option>';
				}
				foreach ( $existing_custom as $custom_option ) {
					echo '<option value="' . esc_attr( $custom_option ) . '" selected>' . esc_attr( $custom_option ) . '</option>';
				}

		} elseif ( array_key_exists( 'selections', $option_value ) && ! $this->has_existing_options( $type, $id, $required ) ) {
			if ( array_key_exists( 'default_value', $option_value ) ) {
				echo '<option value="' . esc_attr( $option_value['default_value'] ) . '" selected>' . esc_attr( $option_value['default_value'] ) . '</option>';
			}
			foreach ( $option_value['selections'] as $selection ) {
				echo '<option value="' . esc_attr( $selection ) . '">' . esc_attr( $selection ) . '</option>';
			}
		} elseif ( ! array_key_exists( 'selections', $option_value ) && $this->has_existing_options( $type, $id, $required ) ) {
			$existing_group_data = self::$group_data;
			$existing_options    = $existing_group_data[ $type ]['options'][ $id ]->$required->$option_key;
			foreach ( $existing_options as $custom_option ) {
				echo '<option value="' . esc_attr( $custom_option ) . '" selected>' . esc_attr( $custom_option ) . '</option>';
			}
		} else {
			if ( array_key_exists( 'default_value', $option_value ) ) {
				echo '<option value="' . esc_attr( $option_value['default_value'] ) . '" selected>' . esc_attr( $option_value['default_value'] ) . '</option>';
			}
		}
		?>

		</select>

		<?php
	}

	public function build_input_list( $type, $current, $option_key, $option_value, $required ) { ?>

		<div class="list-container">
			<div class="list-item" data-nb-list-item="1">
				<?php $start_id = 1; ?>
				<input type="text" name="nb[service][<?php esc_attr_e( $current->id ) ?>][<?php esc_attr_e( $option_value ) ?>][<?php esc_attr_e( $start_id ) ?>][key]"/>
				<input type="text" name="nb[service][<?php esc_attr_e( $current->id ) ?>][<?php esc_attr_e( $option_value ) ?>][<?php esc_attr_e( $start_id ) ?>][value]"/>
				<div class="plus-button"></div>
			</div>
		</div>

	<?php }

	/**
	 * Displays the success message when a notification is saved
	 *
	 * @since 2016.04.11.0
	 * @access public
	 */
	public function add_new_success() {
		if ( isset( $_POST['nb_add_new_submit'] ) ) {
			echo '<div style="clear:both"></div>';
			echo '<div class="notice notice-success is-dismissible"><p>Settings successfully saved.</p></div>';
			echo '<div style="clear:both"></div>';
		}
	}

	/**
	 * Displays the content of the Add New page
	 *
	 * @since 2016.04.10.0
	 * @access public
	 */
	public function display() {
		if ( ! current_user_can( 'manage_options' ) || ! defined( 'ABSPATH' ) ) { exit; }

		$this->add_actions();

		if ( isset( $_GET['group_id'] ) )
			self::$existing = Notifications::get_instance()->get_all_by_group( $_GET['group_id'] );
			$this->get_all_group_data();
		 ?>

		<div id="add-new-selections">
			<?php $this->add_new_success(); ?>

		<form method="post">
			<?php do_action( 'nb_add_new_display_success' ); ?>
			<?php wp_nonce_field( 'nb_add_new', 'nb_add_new' ); ?>

			<!--Begin Group Name-->
			<h1>Notification Group Name</h1>
			<?php echo $this->generate_title_field() ?>
			<!-- End Group Name-->

			<!--Begin Methods-->
			<h1><?php esc_attr_e( $this->methods_header, 'notifybot' ) ?></h1>
			<?php foreach ( Methods::get_instance()->get_all() as $method ) : ?>
				<div id="nb-method-<?php esc_attr_e( $method->id, 'notifybot' ) ?>" class="nb-method-container nb-method-item">

					<!--Begin Method header-->
					<?php $this->generate_header( $method->id, $method->label ); ?>
					<!--End Method header-->

					<!--Begin Method content-->
					<div id="nb-method-<?php esc_attr_e( $method->id, 'notifybot' ) ?>-content">
						<!--Begin Services-->
						<?php foreach ( Services::get_instance()->get_depends( $method->id ) as $service ) : ?>
							<div class="nb-trigger-row">

								<!--Begin Service header-->
								<?php
								$has_input = array(
									'type'          => 'radio',
									'class'         => 'nb-service-select-radio',
									'name'          => 'nb[service]',
									'check_against' => array(),
									'value'         => $service->id
								);

								if ( self::$group_data ) {
									$has_input['check_against'] = self::$group_data['service']['enabled'];
								}

								$this->generate_header( $service->id, $service->label, 'p', $has_input, 'required' );

								?>
								<!--End Service header-->

								<!--Begin Service options-->
								<div class="nb-trigger-options">
									<?php if ( $this->has_requires( $service ) ) : ?>
										<!--Begin required settings-->
										<h2>Required Settings</h2>
										<?php echo __( $this->has_global_requires( $service ) ); ?>
										<?php foreach ( $service->local_settings as $key => $value ) : ?>
											<?php if ( $value['required'] === true ) : ?>
											<!--Begin required setting row-->
											<div class="nb-required-setting-row"
											     id="nb-required-setting-<?php esc_attr_e( $key, 'notifybot' ) ?>">
												<!--Begin required setting label-->
												<div class="nb-column-left">
													<p class="nb-required-setting-label"><?php esc_attr_e( $value['label'], 'notifybot' ) ?></p>
												</div>
												<!--End required setting label-->
												<!--Begin required setting input-->
												<div class="nb-column-right">
													<!--Begin input type check-->
													<?php if ( 'select' == $value['input_type'] ) {
														$this->build_input_select( 'service', $service, $key, $value, 'required' );
													} elseif ( 'text' == $value['input_type'] ) {
														$this->build_input_text( 'service', $service, $key, $value, 'required' );
													} ?>
												</div>
												<!--End required setting input-->
											</div>
											<?php endif; ?>
										<?php endforeach; ?>
										<!--End required settings-->
									<?php endif; ?>
									<?php if ( property_exists( $service, 'local_settings' ) && ! empty( $service->local_settings ) ) : ?>
										<?php if ( $this->has_optional( $service ) ) : ?>
										<!--Begin optional settings-->
										<h2>Optional Settings</h2>
										<?php foreach ( $service->local_settings as $key => $value ) : ?>
											<?php if ( $value['required'] === false ) : ?>

											<!--Begin optional setting row-->
											<div class="nb-optional-setting-row" id="nb-optional-setting-<?php esc_attr_e( $key, 'notifybot' ) ?>">
												<!--Begin optional setting label-->
												<div class="nb-column-left">
													<p class="nb-optional-setting-label"><?php esc_attr_e( $value['label'], 'notifybot' ) ?></p>
												</div>
												<!--End optional setting label-->

												<!--Begin optional setting input-->
												<div class="nb-column-right">
													<!--Begin input type check-->
													<?php
													if ( 'select' == $value['input_type'] ) {
														$this->build_input_select( 'service', $service, $key, $value, 'optional' );
													} elseif ( 'text' == $value['input_type'] ) {

													}
													?>
													<!--End input type check-->
												</div>
												<!--End optional setting input-->

											</div>
											<?php endif; ?>
										<?php endforeach; ?>
										<!--End optional settings-->
										<?php endif; ?>
									<?php endif; ?>
								</div>
								<!--End Service options-->
							</div>
						<?php endforeach; ?>
					</div>
					<!--End Method content-->

				</div>
			<?php endforeach; ?>
			<!--End Methods-->

			<div id="nb-event-wrapper">
				<h1><?php esc_attr_e( $this->event_header, 'notifybot' ) ?></h1>
				<?php foreach ( Events::get_instance()->get_all() as $event ) : ?>
					<div id="nb-event-<?php esc_attr_e( $event->id, 'notifybot' ) ?>" class="nb-event-container nb-event-item">
						<?php $this->generate_header( $event->id, $event->label ); ?>
						<div id="nb-event-<?php esc_attr_e( $event->id, 'notifybot' ) ?>-content">
							<?php foreach ( Triggers::get_instance()->get_depends( $event->id ) as $trigger ) : ?>
								<div id="trigger-row-<?php echo $trigger->id ?>" class="nb-trigger-row">

									<?php
									$has_input = array(
										'type'          => 'checkbox',
										'class'         => 'nb-triggers',
										'name'          => 'nb[trigger][' . esc_attr( $event->id ) . '][' . esc_attr( $trigger->id ) . '][enabled]',
										'check_against' => array(),
										'value'         => 'on',
									);

									if ( self::$group_data ) {
										$has_input['check_against'] = self::$group_data['trigger']['enabled'];
									}

									$this->generate_header( $trigger->id, $trigger->label, 'p', $has_input );

									?>

									<div class="nb-trigger-options">
										<?php if ( property_exists( $trigger, 'local_settings' ) && ! empty( $trigger->local_settings ) ) : ?>
											<h2>Optional Settings</h2>
											<?php foreach ( $trigger->local_settings as $key => $value ) : ?>
											<?php if ( $value['required'] === false ) : ?>
												<?php
												$current = array(
													'trigger' => $trigger,
													'event'   => $event,
												)
												?>

												<!--Begin optional setting row-->
												<div class="nb-optional-setting-row" id="nb-optional-setting-<?php esc_attr_e( $key, 'notifybot' ) ?>">

													<!--Begin optional setting label-->
													<div class="nb-column-left">
														<p class="nb-optional-setting-label"><?php esc_attr_e( $value['label'], 'notifybot' ) ?></p>
													</div>
													<!--End optional setting label-->

													<!--Begin optional setting input-->
													<div class="nb-column-right">

														<!--Begin input type check-->
														<?php if ( 'select' == $value['input_type'] ) : ?>
															<!--Begin select input type-->
															<?php $this->build_input_select( 'trigger', $current, $key, $value, 'optional' ); ?>
															<!--End select input type-->
														<?php elseif ( ( 'text' || 'number' ) == $value['input_type'] ) : ?>
															<!--Begin text input type-->
															<?php $this->build_input_text( 'trigger', $current, $key, $value, 'optional' ); ?>
															<!--End text input type-->
														<?php endif; ?>

													</div>

													<!--End optional setting input-->

												</div>

											<?php endif; ?>
											<?php endforeach; ?>

										<?php endif; ?>
										<!--End required settings-->
										<h2>Message:</h2>
										<div class="nb-trigger-message">
											<?php

											$content = null;

											if ( property_exists( $trigger, 'placeholder' ) ) {
													$content = $trigger->placeholder;
											}

											if ( self::$existing && ! empty( self::$existing ) ) {
												foreach ( self::$existing as $current ) {
													if ( isset( $current->event_trigger ) && $current->event_trigger == $trigger->id && isset( $current->message ) ) {
														$content = $current->message;
													}
												}
											}

											$global_merge_tags = Global_Merge_Tags::$global_tags;
											$trigger_merge_tags = $trigger->merge_tags;

											$merge_tags = array_merge($global_merge_tags, $trigger_merge_tags);
											$merge_tag_buttons = implode( ',', $merge_tags);

											$editor_id = 'notifybot-message-' . $trigger->id;
											$settings  = array(
												'textarea_rows' => 3,
												'tinymce'       => false,
												'media_buttons' => false,
												'textarea_name' => 'nb[trigger][' . $event->id . '][' . $trigger->id . '][message]',
												'quicktags'     => array(
													'buttons' => $merge_tag_buttons
												)

											);
											wp_editor( $content, $editor_id, $settings );
											?>
										</div>
										<div class="nb-webhook-wrapper">
											<h2>Optional Webhook Parameters</h2>
											<div class="webhook-parameter-list" id="webhook-parameters-<?php esc_attr_e( $trigger->id ); ?>">
											<?php if ( ( $this->has_existing_options( 'trigger', $trigger->id, 'optional' ) ) && ( ! empty( self::$group_data['trigger']['options'][$trigger->id]->optional->webhook_parameters ) ) ) : ?>
												<?php foreach ( self::$group_data['trigger']['options'][$trigger->id]->optional->webhook_parameters as $current_param_id => $current_param ) : ?>
													<div class="nb-optional-setting-row webhook-params-item" data-nb-webhook-list="<?php esc_attr_e( $current_param_id ) ?>">
														<input type="text" name="nb[trigger]<?php echo '[' . $event->id . '][' . $trigger->id . ']' ?>[optional][webhook_parameters][<?php esc_attr_e( $current_param_id ) ?>][key]" placeholder="Key" value="<?php esc_html_e($current_param->key); ?>"/>
														<input type="text" name="nb[trigger]<?php echo '[' . $event->id . '][' . $trigger->id . ']' ?>[optional][webhook_parameters][<?php esc_attr_e( $current_param_id ) ?>][value]" placeholder="Value" value="<?php esc_html_e($current_param->value); ?>"/>
														<span class="dashicons dashicons-minus"></span>
														<span class="dashicons dashicons-plus"></span>
													</div>
												<?php endforeach; ?>
											<?php else : ?>
												<div class="nb-optional-setting-row webhook-params-item" data-nb-webhook-list="1">
													<input type="text" name="nb[trigger]<?php echo '[' . $event->id . '][' . $trigger->id . ']' ?>[optional][webhook_parameters][1][key]" placeholder="Key"/>
													<input type="text" name="nb[trigger]<?php echo '[' . $event->id . '][' . $trigger->id . ']' ?>[optional][webhook_parameters][1][value]" placeholder="Value"/>
													<span class="dashicons dashicons-minus"></span>
													<span class="dashicons dashicons-plus"></span>
												</div>
											<?php endif; ?>
											</div>
										</div>

									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

				<?php endforeach; ?>

			</div>
			<?php submit_button( 'Submit Notification', 'primary', 'nb_add_new_submit' ); ?>
		</form>
		</div>
		<?php
	}

	/**
	 * Processes form submissions
	 *
	 * @since 2016.04.11.0
	 * @access public
	 */
	public function process_form_submit() {

		if ( isset( $_POST['nb_add_new_submit'] ) ) {
			if ( ! current_user_can( 'manage_options' ) || ! defined( 'ABSPATH' ) ) { exit; }

			if ( ( ! check_admin_referer( 'nb_add_new', 'nb_add_new' ) ) || ( empty( $_POST['nb']['service'] ) ) )
				return;

			if ( isset( $_GET['group_id'] ) && is_numeric( $_GET['group_id'] ) ) {
				$group_id = intval( $_GET['group_id'] );
			} else {
				$group_id = intval( Notifications::get_instance()->next_id() );
			}

			if ( isset( $_POST['nb-title'] ) ) {
				$title = wp_kses_post( $_POST['nb-title'] );
			} else {
				$errors['nb-title'] = 'Please set a notification title';
			}

			foreach ( $_POST['nb']['trigger'] as $event => $triggers ) {
				foreach ( $triggers as $trigger => $setting ) {
					if ( array_key_exists( 'enabled', $setting ) ) {

						$message = wp_kses_post( $setting['message'] );
						$options = array(
							'service' => array(
								'optional' => null,
								'required' => null,
							),
							'trigger' => array(
								'optional' => null,
								'required' => null,
							),
						);

						if ( is_array( $_POST['nb']['service'] ) ) {
							$service = sanitize_key( key( $_POST['nb']['service'] ) );

							if ( array_key_exists( 'required', $_POST['nb']['service'][ $service ] ) && ! empty( $_POST['nb']['service'][ $service ]['required'] ) ) {
								$required_service_settings      = $_POST['nb']['service'][ $service ]['required'];
								$options['service']['required'] = $required_service_settings;
							}

							if ( array_key_exists( 'optional', $_POST['nb']['service'][ $service ] ) && ! empty( $_POST['nb']['service'][ $service ]['optional'] ) ) {
								$optional_service_settings      = $_POST['nb']['service'][ $service ]['optional'];
								$options['service']['optional'] = $optional_service_settings;
							}

						} else {
							$service = sanitize_key( $_POST['nb']['service'] );
						}

						if ( array_key_exists( 'required', $_POST['nb']['trigger'][ $event ][ $trigger ] ) && ! empty( $_POST['nb']['trigger'][ $event ][ $trigger ]['required'] ) ) {
							$required_trigger_settings      = $_POST['nb']['trigger'][ $event ][ $trigger ]['required'];
							$options['trigger']['required'] = $required_trigger_settings;
						}

						if ( array_key_exists( 'optional', $_POST['nb']['trigger'][ $event ][ $trigger ] ) && ! empty( $_POST['nb']['trigger'][ $event ][ $trigger ]['optional'] ) ) {
							$optional_trigger_settings      = $_POST['nb']['trigger'][ $event ][ $trigger ]['optional'];
							$options['trigger']['optional'] = $optional_trigger_settings;
						}

						$options = json_encode( $options );

						if ( isset( $_GET['group_id'] ) && Notifications::get_instance()->get_all_by_group( $_GET['group_id'] ) ) {
							$group_id = $_GET['group_id'];
							Notifications::get_instance()->update( $group_id, 'testing', $service, 'testing', $trigger, $options, $message );

							if ( Notifications::get_instance()->trigger_exists_in_group( $trigger, $group_id ) === false ) {
								Notifications::get_instance()->insert( $group_id, 'testing', $service, 'testing', $trigger, $options, $message );
							}
						} else {
							Notifications::get_instance()->insert( $group_id, 'testing', $service, 'testing', $trigger, $options, $message );
						}

					} elseif ( isset( $_GET['group_id'] ) ) {
						$deactivated = $this->trigger_deactivated( $trigger );
						if ( $deactivated !== false ) {
							Notifications::get_instance()->delete( $deactivated );
						}
					}
				}

			}
			if ( isset( $title ) ) {
				Global_Settings::get_instance()->set_value( 'nb_group_title_' . $group_id, $title );
			}
		}
	}

	/**
	 * Checks if a previously activated trigger is deactivated
	 *
	 * @since 2016.04.11.0
	 * @access public
	 *
	 * @param string $current_trigger The current trigger ID being checked
	 *
	 * @return bool The trigger ID if deactivated.  False otherwise.
	 */
	public function trigger_deactivated( $current_trigger ) {
		if ( ! current_user_can( 'manage_options' ) || ! defined( 'ABSPATH' ) ) { exit; }
		if ( isset( $_GET['group_id'] ) && Notifications::get_instance()->get_all_by_group( $_GET['group_id'] ) ) {
			$existing = Notifications::get_instance()->get_all_by_group( $_GET['group_id'] );
			foreach ( $existing as $current ) {
				if ( ( $current->event_trigger == $current_trigger ) ) {
					return $current->id;

				}
			}
			return false;
		} else {
			return false;
		}
	}

	/**
	 * Generates the quicktags for NotifyBot merge tags in the message content
	 *
	 * @since 2016.04.11.0
	 * @access public
	 */
	public function run_quicktags() {
		if ( wp_script_is('quicktags' ) ) { ?>

			<script type="text/javascript">
				<?php foreach ( Triggers::get_instance()->get_merge_tags() as $trigger => $tags ) : ?>
					<?php $tags = array_merge( $tags, Global_Merge_Tags::$global_tags ) ?>
					<?php foreach ( $tags as $tag ) : ?>
						QTags.addButton( '<?php echo $tag ?>', '<?php echo $tag ?>', '<?php echo '{' . $tag . '}' ?>', '', '', '<?php echo $tag ?>', '', 'notifybot-message-<?php echo $trigger ?>' );
					<?php endforeach; ?>
				<?php endforeach; ?>
			</script>

		<?php }
	}


}