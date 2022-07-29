<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class Field {

	public $properties = array();

	public function __construct( $meta_box ) {
		$this->meta_box = $meta_box;
		$this->create_field();
	}

	public function create_field() {
		$this->meta_box->add_field( $this->properties );
	}
}
