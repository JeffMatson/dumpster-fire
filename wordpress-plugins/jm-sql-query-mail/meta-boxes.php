<?php

add_action( 'cmb2_admin_init', 'cmb2_sample_metaboxes' );
/**
 * Define the metabox and field configurations.
 */
function cmb2_sample_metaboxes() {

	$query_details = new_cmb2_box( array(
		'id'            => 'query_details',
		'title'         => __( 'Details', 'cmb2' ),
		'object_types'  => array( 'sql_query_mail' ),
		'context'       => 'side',
		'priority'      => 'high',
		'show_names'    => true,
	) );

	$sql_query = new_cmb2_box( array(
		'id'            => 'sql_query',
		'title'         => __( 'SQL Query', 'cmb2' ),
		'object_types'  => array( 'sql_query_mail' ),
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true,
	) );

	$options = new_cmb2_box( array(
		'id'            => 'options',
		'title'         => __( 'Options', 'cmb2' ),
		'object_types'  => array( 'sql_query_mail' ),
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true,
	) );

	$query_details->add_field( array(
		'name'    => 'Interval',
		'desc'    => 'Interval, in seconds, to run this query. (optional)',
		'default' => '3600',
		'id'      => 'query_interval',
		'type'    => 'text_number',
	) );

	$options->add_field( array(
		'name'    => 'Interval',
		'desc'    => 'Interval, in seconds, to run this query. (optional)',
		'default' => '',
		'id'      => 'query_interval',
		'type'    => 'text_number',
	) );

	$options->add_field( array(
		'name'    => 'Run Query After Processing',
		'desc'    => 'Query to run after processing. (optional)',
		'default' => '3600',
		'id'      => 'run_after_query',
		'type'    => 'textarea_code',
	) );

	$sql_query->add_field( array(
		'name'       => __( 'SQL Query', 'cmb2' ),
		'desc'       => 'Enter the full query to run. DO NOT include a semicolon at the end.',
		'id'         => 'sql_query',
		'type'       => 'textarea_code',
		'show_on_cb' => 'cmb2_hide_if_no_cats',
	) );

	$mail_settings->add_field( array(
		'name'       => __( 'From', 'cmb2' ),
		'desc'       => 'From address. {sql:column_name} tags supported',
		'id'         => 'from_address',
		'type'       => 'text',
		'show_on_cb' => 'cmb2_hide_if_no_cats',
	) );

	$mail_settings->add_field( array(
		'name' => __( 'To', 'cmb2' ),
		'desc' => 'To address. {sql:column_name} tags supported',
		'id'   => 'to_address',
		'type' => 'text',
	) );

	$mail_settings->add_field( array(
		'name' => __( 'CC', 'cmb2' ),
		'desc' => 'CC address. {sql:column_name} tags supported',
		'id'   => 'cc',
		'type' => 'text',
	) );

	$mail_settings->add_field( array(
		'name' => __( 'BCC', 'cmb2' ),
		'desc' => 'BCC address. {sql:column_name} tags supported',
		'id'   => 'bcc',
		'type' => 'text',
	) );

	$mail_settings->add_field( array(
		'name' => __( 'Subject', 'cmb2' ),
		'desc' => 'Subject. {sql:column_name} tags supported',
		'id'   => 'subject',
		'type' => 'text',
	) );

}
