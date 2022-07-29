<?php

namespace JM_SQL_Query_Mail\Meta_Boxes;

class Actions extends Meta_Box {

	public $id = 'actions';
	public $title = 'Actions';
	public $context = 'side';

	public function init_fields( $meta_box ) {
		new Fields\Query_Results( $meta_box );
	}

}
