<?php

namespace JM_SQL_Query_Mail\Meta_Boxes;

class Meta_Box {

	public $post_types = array(
		'sql_query_mail',
	);

	public $context = 'normal';

	public $priority = 'high';

	public $show_names = true;

	public function init() {
		$meta_box = $this->create_meta_box();
		$this->init_fields( $meta_box );
	}

	public function create_meta_box() {
		return new_cmb2_box( array(
			'id'            => $this->id,
			'title'         => $this->title,
			'object_types'  => $this->post_types,
			'context'       => $this->context,
			'priority'      => $this->priority,
			'show_names'    => $this->show_names,
		) );
	}
}
