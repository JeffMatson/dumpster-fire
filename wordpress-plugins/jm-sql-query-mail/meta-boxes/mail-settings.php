<?php

namespace JM_SQL_Query_Mail\Meta_Boxes;

class Mail_Settings extends Meta_Box {

	public $id = 'mail_settings';
	public $title = 'Mail Settings';

	public function init_fields( $meta_box ) {
		new Fields\From_Address( $meta_box );
		new Fields\To_Address( $meta_box );
		new Fields\CC( $meta_box );
		new Fields\BCC( $meta_box );
		new Fields\Subject( $meta_box );
	}
}
