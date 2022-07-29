<?php

namespace NotifyBot\Views;

use NotifyBot\Core;
use NotifyBot\Models\Global_Settings;
use NotifyBot\Models\Notifications;
use NotifyBot\Notifications\Services;

class Settings extends View_Controller {

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return static::$instance;
	}

	public function enqueue_scripts() {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'notifybot-settings' ) {
			return;
		}

		$nb_url = Core::get_instance()->url();
		wp_enqueue_style( 'notifybot-settings', $nb_url . 'css/settings.css', array( 'select2', 'notifybot' ) );
		wp_enqueue_style( 'notifybot', $nb_url . 'css/notifybot.css' );

		wp_enqueue_script( 'nb-settings', $nb_url . 'js/settings.js', array( 'jquery', 'select2' ) );
		wp_enqueue_style( 'nb-settings', $nb_url . 'css/settings.css', array( 'select2', 'notifybot' ) );
	}

	public function add_actions() {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'notifybot-settings' ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'process_form_submit' ) );
	}

	public function get_global_settings() {

		$global_settings = array();

		foreach ( Services::get_instance()->get_all() as $service ) {
			if ( isset( $service->global_settings ) ) {
				$global_settings[] = $service->global_settings;
			}
		}

		$global_settings = apply_filters( 'nb_global_settings', $global_settings );
		return $global_settings;

	}

	public function display() {
		if ( ! current_user_can( 'manage_options' ) || ! defined( 'ABSPATH' ) ) { exit; }
		?>
		<div id="nb-settings-wrapper">
				<div class="nb-setting-group" id="nb-setting-group-license">
					<h2>NotifyBot Licensing</h2>
					<h3>License Keys</h3>
					<form method="post">
						<?php wp_nonce_field( 'nb_activate_nonce', 'nb_activate_nonce' ); ?>
					<div class="nb-setting-row">
						<div class="nb-column-left">
							<p class="nb-required-setting-label">NotifyBot License Key</p>
						</div>
						<div class="nb-column-right">
							<input type="text" name="nb_license_key" class="nb_license_key_input" value="<?php echo get_option( 'nb_license_key' ); ?>"/>
							<?php if ( get_option( 'nb_license_status') == 'valid' ) {
								submit_button( 'Deactivate', 'button-red', 'nb_license_deactivate' );
							} else {
								submit_button( 'Activate', 'button-green', 'nb_license_activate' );
							}
							?>
						</div>
					</div>
					</form>
					<?php do_action( 'nb_license_setting' ); ?>
				</div>
			<form method="post">
				<?php wp_nonce_field( 'nb_global_settings_nonce', 'nb_global_settings_nonce' ); ?>
				<div class="nb-setting-group" id="nb-setting-group-license">
					<h2>General Settings</h2>
					<h3>Stealth Mode</h3>
						<div class="nb-setting-row">
							<div class="nb-column-left">
								<p class="nb-required-setting-label">Stealth Mode</p>
							</div>
							<div class="nb-column-right">
								<?php $stealth_mode_active = Global_Settings::get_instance()->get_value( 'stealth_mode_active' ) ?>
								<input type="checkbox" style="margin-bottom:13px; margin-top: 13px;" name="nb-global-setting[stealth_mode_active]"<?php echo ( $stealth_mode_active == 'on' ) ? esc_attr( 'checked' ) : ''; ?>/>
							</div>
						</div>
						<div class="nb-setting-row">
							<div class="nb-column-left">
								<p class="nb-required-setting-label">Allowed User List</p>
							</div>
							<div class="nb-column-right">
								<select multiple class="nb-select2 nb-stealth-allowed-users" name="nb-global-setting[stealth_allowed_users][]" title="" data-placeholder="Users here will be allowed to access NotifyBot">
									<?php $current_user_login = wp_get_current_user()->user_login; ?>
									<?php if ( Global_Settings::get_instance()->value_exists( 'stealth_allowed_users' ) ) : ?>
										<?php $current_allowed_users = json_decode( Global_Settings::get_instance()->get_value( 'stealth_allowed_users' ) ); ?>
									<?php else : ?>
										<?php $current_allowed_users = array(); ?>
									<?php endif; ?>
									<?php foreach ( $this->list_users() as $user ) : ?>
										<?php if ( $user == $current_user_login || in_array( $user, $current_allowed_users ) ) : ?>
											<option value="<?php esc_attr_e( $user ) ?>" selected><?php esc_attr_e( $user ) ?></option>
										<?php else : ?>
											<option value="<?php esc_attr_e( $user ) ?>"><?php esc_attr_e( $user ) ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					<?php do_action( 'nb_license_setting' ); ?>
				</div>
				<?php foreach ( $this->get_global_settings() as $setting_group ) : ?>
					<div class="nb-setting-group" id="nb-setting-group-<?php echo $setting_group['id'] ?>">
						<h2><?php echo $setting_group['header'] ?></h2>
						<?php foreach ( $setting_group['sections'] as $section ) : ?>
							<h3><?php echo $section['label'] ?></h3>
							<?php foreach ( $section['options'] as $option_id => $option ) : ?>
								<div class="nb-setting-row">
									<div class="nb-column-left">
										<p class="nb-required-setting-label"><?php echo $option['label'] ?></p>
									</div>
									<div class="nb-column-right">
										<?php $exists = Global_Settings::get_instance()->value_exists( $option_id ); ?>
										<?php if ( $exists == true ) :
											$field_value = ' value="' . Global_Settings::get_instance()->get_value( $option_id ) . '"';
										else :
											$field_value = '';
										endif;
										if ( ( 'text' || 'password' == $option['input_type'] ) ) : ?>
											<input type="<?php echo $option['input_type'] ?>" style="width:49%" name="nb-global-setting[<?php echo $option_id ?>]"<?php echo $field_value ?>/>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				<?php submit_button( 'Save Settings', 'primary', 'nb_global_settings_save' ); ?>
			</form>
			<?php
			$log_locations = Notifications::get_instance()->get_all_log_locations();
			if ( empty( $log_locations ) ) {
				$log_locations = 'Logging not currently configured';
			}

			$rss_slugs = Notifications::get_instance()->get_all_rss_slugs();
			if ( empty( $rss_slugs ) ) {
				$rss_slugs = 'RSS not currently configured';
			}

			$general_info = array(
				'logging' => array(
					'label' => 'Log Information',
					'info'  => array(
						'log_locations' => array(
							'label' => 'Log Locations',
							'value' => $log_locations,
						),
					),
				),
				'rss' => array(
					'label' => 'RSS Information',
					'info' => array(
						'rss_locations' => array(
							'label' => 'RSS Slugs',
							'value' => $rss_slugs,
						),
					),
				),
				'server' => array(
					'label' => 'Server Information',
					'info'  => array(
						'php_version' => array(
							'label' => 'PHP Version',
							'value' => phpversion(),
						),
						'wp_version' => array(
							'label' => 'WordPress Version',
							'value' => get_bloginfo('version'),
						),
					),
				),
			);

			$general_info = apply_filters( 'nb_global_settings_info', $general_info );

			?>
			<div class="nb-setting-group">
				<h2>General Information</h2>

				<?php foreach ( $general_info as $section ) : ?>
					<h3><?php echo $section['label'] ?></h3>
					<div class="nb-setting-row">
					<?php foreach ( $section['info'] as $section_info ) : ?>
						<div class="nb-column-left">
							<p class="nb-required-setting-label"><?php echo $section_info['label'] ?></p>
						</div>
						<div class="nb-column-right">
							<?php if ( is_array($section_info['value']) ) : ?>
								<ul>
								<?php foreach ( $section_info['value'] as $list_item ) : ?>
									<li><?php echo $list_item; ?></li>
								<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<p><?php echo $section_info['value'] ?></p>
							<?php endif; ?>
						</div>
					<?PHP endforeach; ?>
					</div>
				<?php endforeach; ?>

			</div>
		</div>
	<?php
	}

	public function process_form_submit() {
		if( isset( $_POST['nb_global_settings_save'] ) ) {
			if ( ! current_user_can( 'manage_options' ) || ! defined( 'ABSPATH' ) ) { exit; }

			// run a quick security check
			if( ! check_admin_referer( 'nb_global_settings_nonce', 'nb_global_settings_nonce' ) ) { return; }

			foreach ( $_POST['nb-global-setting'] as $key => $value ) {
				Global_Settings::get_instance()->set_value( $key, $value );
			}

			if ( ! isset( $_POST['nb-global-setting']['stealth_mode_active'] ) ) {
				Global_Settings::get_instance()->set_value( 'stealth_mode_active', 'off' );
			}

		}
	}

}
