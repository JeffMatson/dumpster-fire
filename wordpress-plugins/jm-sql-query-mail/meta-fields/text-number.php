<?php
namespace JM_SQL_Query_Mail\Meta_Fields;

class Text_Number {

	public function sanitize( $null, $new ) {
		$new = preg_replace( '/[^0-9]/', '', $new );
		return $new;
	}

	public function render( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		echo $field_type_object->input( array( 'class' => 'cmb2-text-small', 'type' => 'number' ) );
	}

}
