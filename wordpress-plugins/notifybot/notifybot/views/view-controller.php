<?php

namespace NotifyBot\Views;
use NotifyBot\Core;
use NotifyBot\Models\Notifications;

abstract class View_Controller {

	public function __construct() {
		$this->add_actions();
		add_action( 'admin_enqueue_scripts', array( $this, 'register_global_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function register_global_scripts() {
		$core = Core::get_instance();

//		wp_register_script( 'select2', $core->url() . 'js/includes/select2.min.js', array( 'jquery' ) );
		wp_register_script( 'angular', $core->url() . 'js/includes/angular.min.js', array() );
		wp_register_script( 'angular-sanitize', $core->url() . 'js/includes/textAngular-sanitize.min.js', array() );
//		wp_register_script( 'bootstrap', $core->url() . 'js/includes/bootstrap.min.js', array() );
		wp_register_script( 'angular-ui-select', $core->url() . 'js/includes/select.min.js', array() );
		wp_register_script( 'angular-to-array-filter', $core->url() . 'js/includes/toArrayFilter.js', array() );
		wp_register_script( 'angular-slideables', $core->url() . 'js/includes/angularSlideables.js', array() );
		wp_register_script( 'angular-ui-bootstrap', $core->url() . 'js/includes/ui-bootstrap.min.js', array() );
		wp_register_script( 'angular-animate', $core->url() . 'js/includes/angular-animate.min.js', array() );
//		wp_register_script( 'textangular', $core->url() . 'js/includes/textAngular.min.js', array() );
//		wp_register_script( 'textangular-rangy', $core->url() . 'js/includes/textAngular-rangy.min.js', array() );
//		wp_register_script( 'jquery-collapse', $core->url() . 'js/includes/jquery.collapse.js', array( 'jquery' ) );
//		wp_register_script( 'jquery-validate', $core->url() . 'js/includes/jquery.validate.min.js', array( 'jquery' ) );

//		wp_register_style( 'select2', $core->url() . 'css/includes/select2.min.css' );
		wp_register_style( 'notifybot', $core->url() . 'css/notifybot.css' );
		wp_register_style( 'bootstrap', $core->url() . 'css/includes/bootstrap.min.css' );
//		wp_register_style( 'selectize', $core->url() . 'css/includes/selectize.css' );
		wp_register_style( 'bootstrap-theme', $core->url() . 'css/includes/bootstrap-theme.min.css' );
		wp_register_style( 'angular-ui-select', $core->url() . 'css/includes/select.min.css' );
//		wp_register_style( 'textangular', $core->url() . 'css/includes/textAngular.css' );

	}

	public function add_actions() {}
	public function enqueue_scripts() {}
	public function display() {}

	public function add_new_button() {
		$url = add_query_arg( array(
			'page' => 'notifybot-add-new',
			'id'   => intval( Notifications::get_instance()->next_id() )
		), admin_url( 'admin.php' ) );

		return '<a class="page-title-action" href="' . esc_url( $url ) . '">Add New</a>';
	}

	public function list_users() {
		$users     = get_users( array( 'role' => 'Administrator' ) );
		$user_list = array();
		foreach ( $users as $user ) {
			$user_list[] = $user->user_login;
		}

		return $user_list;
	}
}

