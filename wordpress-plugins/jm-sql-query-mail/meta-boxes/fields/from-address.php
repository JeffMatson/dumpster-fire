<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class From_Address extends Field {

	public $properties = array(
		'name' => 'From',
		'desc' => 'From address. {sql:column_name} tags supported',
		'id'   => 'from_address',
		'type' => 'text',
	);

}
