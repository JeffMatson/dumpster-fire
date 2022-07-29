<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class Subject extends Field {

	public $properties = array(
		'name' => 'Subject',
		'desc' => 'Subject. {sql:column_name} tags supported',
		'id'   => 'subject',
		'type' => 'text',
	);

}
