<?php

namespace NotifyBot\Views;
use NotifyBot\Model;
use NotifyBot\Models\Global_Settings;
use NotifyBot\Notifications\Methods;
use NotifyBot\Notifications\Services;
use NotifyBot\Notifications\Events;
use NotifyBot\Notifications\Triggers;
use NotifyBot\Views;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class List_Table extends \WP_List_Table {
	/**
	 * Prepare the items for the table to process
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->table_data();
		usort( $data, array( &$this, 'sort_data' ) );

		$per_page     = 20;
		$current_page = intval( $this->get_pagenum() );
		$total_items  = count( $data );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );

		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to the list table
	 *
	 * @return array Columns to be displayed
	 */
	public function get_columns() {
		$columns = array(
			'group_id'      => 'ID',
			'group_title'   => 'Title',
			'event_trigger' => 'Triggers',
			'service'       => 'Service',
		);

		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array( 'title' => array( 'title', false ) );
	}

	/**
	 * Get the table data
	 *
	 * @return array
	 */
	private function table_data() {
		global $wpdb;
		$table = Model::get_instance()->table_name( 'notify' );
		$data  = $wpdb->get_results( "SELECT id, group_id, method, service, event, event_trigger FROM $table GROUP BY group_id", 'ARRAY_A' );

		foreach ( $data as $key => $value ) {

			$title_link = add_query_arg(
				array(
					'page'     => 'notifybot-add-new',
					'group_id' => intval( $value['group_id'] ),
					'action'   => 'edit',
				)
			);

			$data[ $key ]['group_title']   = '<a class="nb-list-table-title" href="' . esc_url( $title_link ) . '">' . Global_Settings::get_instance()->get_value( 'nb_group_title_' . $value['group_id'] ) . '</a>';
			if ( Services::get_instance()->exists( $data[ $key ]['service'] ) ) {
				$data[ $key ]['service'] = Services::get_instance()->get_service( $data[ $key ]['service'] )->label;
			} else {
				$data[ $key ]['service'] = 'Add-On Inactive: ' . $data[ $key ]['service'];
			}

			$trigger_group_id = $data[ $key ]['group_id'];
			$triggers  = $wpdb->get_results( $wpdb->prepare( "SELECT event_trigger FROM $table WHERE group_id = '%s'", $trigger_group_id ), 'ARRAY_A' );

			$trigger_list = '<ul>';
			foreach ($triggers as $trigger ) {
				if ( Triggers::get_instance()->exists( $trigger['event_trigger'] ) ) {
					$trigger_label = Triggers::get_instance()->get_trigger( $trigger['event_trigger'] )->label;
				} else {
					$trigger_label = 'Add-On Inactive: ' . $trigger['event_trigger'];
				}
				$trigger_list .= '<li>' . $trigger_label . '</li>';
			}
			$trigger_list .= '</ul>';

 			$data[ $key ]['event_trigger'] = $trigger_list;
		}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array $item Data
	 * @param  string $column_name Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'group_id' :
			case 'group_title' :
			case 'event_trigger' :
				return wp_kses_post( $item[ $column_name ] );
			case 'service':
				return wp_kses_post( $item[ $column_name ] );
			default :
				return print_r( $item, true );
		}
	}

	public function column_group_title($item) {

		$edit_url = add_query_arg( array(
				'page'     => 'notifybot-add-new',
				'group_id' => $item['group_id'],
				'action'   => 'edit',
			)
		);

		$delete_url = add_query_arg( array (
				'page'     => 'notifybot',
				'group_id' => $item['group_id'],
				'action'   => 'delete',
			)
		);

		$actions = array(
			'edit'      => '<a href="' . esc_url( $edit_url ) . '">Edit</a>',
			'delete'    => '<a href="' . esc_url( $delete_url ) . '">Delete</a>',
		);

		return sprintf( '%1$s %2$s', $item['group_title'], $this->row_actions( $actions ) );
	}
}

