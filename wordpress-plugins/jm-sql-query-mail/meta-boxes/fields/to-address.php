<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class To_Address extends Field {

	public $properties = array(
		'name' => 'To',
		'desc' => 'To address. {sql:column_name} tags supported',
		'id'   => 'to_address',
		'type' => 'text',
	);

}
