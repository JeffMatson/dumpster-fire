<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class SQL_Query extends Field {

	public $properties = array(
		'name'       => 'SQL Query',
		'desc'       => 'Enter the full query to run. DO NOT include a semicolon at the end.',
		'id'         => 'sql_query',
		'type'       => 'textarea_code',
	);

}
