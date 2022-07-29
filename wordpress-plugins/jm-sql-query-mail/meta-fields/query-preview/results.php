<?php
namespace JM_SQL_Query_Mail\Meta_Fields\Query_Preview;

class Results {

	public function render( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$db = \JM_SQL_Query_Mail\SQL_DB::get_instance();
		if ( is_numeric( $object_id ) ) {
			$query = get_post_meta( $object_id, 'sql_query', true );

			if ( ! empty( $query ) ) {

				$query_results = $db->get_results( $query );

				if ( is_array( $query_results ) && ! empty( $query_results ) ) {
					echo '<ul>';
					foreach ( $query_results[0] as $key => $value ) {
						echo '<li><strong>' . esc_html( $key ) . ':</strong> ' . esc_html( $value ) . '</li>';
					}
					echo '</ul>';
				} else {
					echo 'No results found.';
				}
			} else {
				echo 'No preview available. Query does not exist.';
			}
		}
	}

}
