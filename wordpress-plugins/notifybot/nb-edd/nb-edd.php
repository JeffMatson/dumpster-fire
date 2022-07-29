<?php
/*
Plugin Name: NotifyBot Easy Digital Downloads Add-On
Plugin URI: https://notifybot.io
Description: Adds support for Easy Digital Downloads to NotifyBot
Version: 2016.04.11.0
Author: NotifyBot
Author URI: https://notifybot.io
License: GPLv2 or later
*/

define( 'NB_EDD', 'Easy Digital Downloads Add-On' );
define( 'NB_EDD_DIR', plugin_dir_path( __FILE__ ) );

add_action( 'nb_loaded', 'run_nb_edd' );

function run_nb_edd() {

	add_action('nb_license_setting','nb_edd_license_form');
	add_action( 'admin_init', 'nb_edd_updater', 0 );
	add_action( 'admin_init', 'nb_edd_activate_license' );
	add_action( 'admin_init', 'nb_edd_deactivate_license' );

	foreach ( glob( plugin_dir_path( __FILE__ ) . 'events/*.php' ) as $filename ) {
		require_once( $filename );
	}

	foreach ( glob( plugin_dir_path( __FILE__ ) . 'triggers/*.php' ) as $filename ) {
		require_once( $filename );
	}

}

function nb_edd_updater() {

	$license_key = trim( get_option( 'nb_edd_license_key' ) );

	$nb_updater = new \NotifyBot\Includes\Updater( NB_API_URL, __FILE__, array(
			'version'   => get_plugin_data( __FILE__ )['Version'],
			'license'   => $license_key,
			'item_name' => NB_EDD,
			'author'    => 'NotifyBot'
		)
	);

}

function nb_edd_license_form() { ?>
	<form method="post">
		<?php wp_nonce_field( 'nb_activate_nonce', 'nb_activate_nonce' ); ?>
		<div class="nb-setting-row">
			<div class="nb-column-left">
				<p class="nb-required-setting-label">Easy Digital Downloads Add-On License Key</p>
			</div>
			<div class="nb-column-right">
				<input type="text" name="nb_edd_license_key" class="nb_license_key_input" value="<?php echo get_option( 'nb_edd_license_key' ); ?>">
				<?php if ( get_option( 'nb_edd_license_status') == 'valid' ) {
					submit_button( 'Deactivate', 'button-red', 'nb_edd_license_deactivate' );
				} else {
					submit_button( 'Activate', 'button-green', 'nb_edd_license_activate' );
				}
				?>
			</div>
		</div>
	</form>
	<?php
}

function nb_edd_activate_license() {

	if( isset( $_POST['nb_edd_license_activate'] ) ) {

		if( ! check_admin_referer( 'nb_activate_nonce', 'nb_activate_nonce' ) )
			return;


		if ( isset( $_POST['nb_edd_license_key'] ) )
			update_option( 'nb_edd_license_key', sanitize_key( $_POST['nb_edd_license_key'] ) );

		$license = trim( get_option( 'nb_edd_license_key' ) );

		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( NB_EDD ),
			'url'       => home_url()
		);

		$response = wp_remote_post( NB_API_URL, array( 'timeout' => 15, 'body' => $api_params ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'nb_edd_license_status', $license_data->license );

	}
}

function nb_edd_deactivate_license() {

	if( isset( $_POST['nb_edd_license_deactivate'] ) ) {

		if( ! check_admin_referer( 'nb_activate_nonce', 'nb_activate_nonce' ) )
			return;

		$license = trim( get_option( 'nb_edd_license_key' ) );

		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( NB_EDD ),
			'url'       => home_url()
		);

		$response = wp_remote_post( NB_API_URL, array( 'timeout' => 15, 'body' => $api_params ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( $license_data->license == 'deactivated' )
			delete_option( 'nb_edd_license_status' );

	}
}
