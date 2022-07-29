<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class CC extends Field {

	public $properties = array(
		'name' => 'CC',
		'desc' => 'CC address. {sql:column_name} tags supported',
		'id'   => 'cc',
		'type' => 'text',
	);

}
