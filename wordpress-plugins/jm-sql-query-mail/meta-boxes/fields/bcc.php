<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class BCC extends Field {

	public $properties = array(
		'name' => 'BCC',
		'desc' => 'BCC address. {sql:column_name} tags supported',
		'id'   => 'bcc',
		'type' => 'text',
	);

}
