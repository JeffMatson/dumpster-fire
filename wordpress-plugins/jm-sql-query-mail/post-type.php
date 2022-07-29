<?php

namespace JM_SQL_Query_Mail;

class Post_Type {

	public $labels = array(
		'name'                  => 'SQL Query to Mail',
		'singular_name'         => 'SQL Query to Mail',
		'menu_name'             => 'SQL Query to Mail',
		'name_admin_bar'        => 'SQL Query to Mail',
		'archives'              => 'SQL Query to Mail Archives',
		'attributes'            => 'SQL Query to Mail Attributes',
		'parent_item_colon'     => 'Parent Item:',
		'all_items'             => 'All Items',
		'add_new_item'          => 'Add New Item',
		'add_new'               => 'Add New',
		'new_item'              => 'New Item',
		'edit_item'             => 'Edit Item',
		'update_item'           => 'Update Item',
		'view_item'             => 'View Item',
		'view_items'            => 'View Items',
		'search_items'          => 'Search Item',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into item',
		'uploaded_to_this_item' => 'Uploaded to this item',
		'items_list'            => 'Items list',
		'items_list_navigation' => 'Items list navigation',
		'filter_items_list'     => 'Filter items list',
	);

	public $supports = array(
		'title',
		'editor',
		'revisions',
		'custom-fields',
	);

	public $taxonomies = array(
		'category',
		'post_tag',
	);

	public function get_settings() {
		return array(
			'label'                 => 'SQL Query to Mail',
			'description'           => 'SQL Query to Mail',
			'labels'                => $this->labels,
			'supports'              => $this->supports,
			'taxonomies'            => $this->taxonomies,
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
	}

	public function init() {
		register_post_type( 'sql_query_mail', $this->get_settings() );
	}

}
