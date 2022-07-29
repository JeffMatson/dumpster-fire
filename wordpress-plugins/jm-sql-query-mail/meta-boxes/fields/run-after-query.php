<?php
namespace JM_SQL_Query_Mail\Meta_Boxes\Fields;

class Run_After_Query extends Field {

	public $properties = array(
		'name'    => 'Run Query After Processing',
		'desc'    => 'Query to run after processing. (optional)',
		'id'      => 'run_after_query',
		'type'    => 'textarea_code',
	);

}
