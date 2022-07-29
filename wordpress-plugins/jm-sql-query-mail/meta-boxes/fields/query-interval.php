<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class Query_Interval extends Field {

	public $properties = array(
		'name'    => 'Interval',
		'desc'    => 'Interval, in seconds, to run this query. (optional)',
		'default' => '3600',
		'id'      => 'query_interval',
		'type'    => 'text_number',
	);

}
