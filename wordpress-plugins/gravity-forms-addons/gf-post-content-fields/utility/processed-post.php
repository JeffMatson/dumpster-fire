<?php
namespace GF_Post_Content_Fields\Utility;

class Processed_Post {

	public $ID           = '';
	public $post_title   = '';
	public $post_content = '';

	public function __construct( $post ) {
		$this->included_items( $post );
	}

	public function included_items( $post_obj, $keys = array( 'ID', 'post_title', 'post_content' ) ) {
		foreach ( $keys as $key ) {
			$this->$key = $post_obj->$key;
		}
	}

}
