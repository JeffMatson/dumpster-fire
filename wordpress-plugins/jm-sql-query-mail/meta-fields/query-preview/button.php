<?php
namespace JM_SQL_Query_Mail\Meta_Fields\Query_Preview;

class Button {

	public function render( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		echo '<button name="test_query" value="true">Test Query</button>';
	}

}
