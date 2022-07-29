<?php

namespace NotifyBot;

class Global_Merge_Tags {

	public static $global_tags = array(
		'site_name',
		'site_url',
		'user'
	);

	/**
	 * Global merge tag
	 * {site_name}
	 *
	 * @param array $replacements The merge tags, before adding this one.
	 *
	 * @return array $replacements Contains our merge tags, with this one added.
	 */
	public static function site_name( $replacements ) {
		$replacements['{site_name}'] = get_bloginfo();
		return $replacements;
	}

	/**
	 * Global merge tag
	 * {site_url}
	 *
	 * @param array $replacements The merge tags, before adding this one.
	 *
	 * @return array $replacements Contains our merge tags, with this one added.
	 */
	public static function site_url( $replacements ) {
		$replacements['{site_url}'] = home_url();
		return $replacements;
	}

	/**
	 * Global merge tag
	 * {user}
	 *
	 * @param array $replacements The merge tags, before adding this one.
	 *
	 * @return array $replacements Contains our merge tags, with this one added.
	 */
	public static function user( $replacements ) {
		$replacements['{user}'] = wp_get_current_user()->user_login;
		return $replacements;
	}

}

// Instantiates merge tags
$global_merge_tags = new Global_Merge_Tags();

// Filter the merge tags
add_filter( 'nb_process_merge_tag_replacements', array( $global_merge_tags, 'site_name' ), 10, 1 );
add_filter( 'nb_process_merge_tag_replacements', array( $global_merge_tags, 'site_url' ), 10, 1 );
add_filter( 'nb_process_merge_tag_replacements', array( $global_merge_tags, 'user' ), 10, 1 );