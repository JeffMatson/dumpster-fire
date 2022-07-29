<?php

namespace NotifyBot\Notifications\Triggers;

class Trigger {

	public $id;
	public $label;
	public $depends_on;
	public $local_settings;
	public $global_settings;
	public $merge_tags = array();

	public function __construct() {
		if ( method_exists( $this, 'local_settings' ) )
			$this->local_settings = $this->local_settings();

		if ( method_exists( $this, 'global_settings' ) )
			$this->global_settings = $this->global_settings();
	}

	public function get_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		$plugin_names = array();

		foreach ( $all_plugins as $plugin ) {
			$plugin_names[] = $plugin['Name'];
		}

		return $plugin_names;
	}

	public function get_optional_users() {
		$users     = get_users();
		$user_list = array();
		foreach ( $users as $user ) {
			$user_list[] = $user->display_name;
		}

		return $user_list;
	}

	public function get_optional_page() {
		$pages     = get_pages();
		$page_list = array();
		foreach ( $pages as $page ) {
			$page_list[] = $page->post_title;
		}

		return $page_list;
	}

	public function get_optional_post() {
		$posts     = get_posts();
		$post_list = array();
		foreach ( $posts as $post ) {
			$post_list[] = $post->post_title;
		}

		return $post_list;
	}
	public function get_optional_post_type() {
		return get_post_types();
	}

	public function get_themes() {
		$all_themes = wp_get_themes();
		$theme_names = array();

		foreach ( $all_themes as $theme ) {
			$theme_names[] = $theme['Name'];
		}

		return $theme_names;
	}
	
	
}
