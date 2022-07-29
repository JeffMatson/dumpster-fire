<?php

namespace JM_SQL_Query_Mail\Meta_Boxes;

class Options extends Meta_Box {

	public $id = 'options';
	public $title = 'Options';

	public function init_fields( $meta_box ) {
		new Fields\SQL_Query( $meta_box );
		new Fields\Query_Interval( $meta_box );
		new Fields\Run_After_Query( $meta_box );
	}

}
