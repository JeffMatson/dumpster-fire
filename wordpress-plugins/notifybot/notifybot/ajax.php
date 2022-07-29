<?php

add_action( 'wp_ajax_get_methods', 'nb_ajax_get_methods' );
add_action( 'wp_ajax_get_services', 'nb_ajax_get_services' );
add_action( 'wp_ajax_get_events', 'nb_ajax_get_events' );
add_action( 'wp_ajax_get_triggers', 'nb_ajax_get_triggers' );
add_action( 'wp_ajax_get_trigger_message', 'nb_ajax_get_trigger_message' );
add_action( 'wp_ajax_save_trigger', 'nb_ajax_save_trigger' );

function nb_ajax_get_methods() {
	$methods = \NotifyBot\Notifications\Methods::get_instance()->get_all();
	echo json_encode( $methods );
	wp_die();
}

function nb_ajax_get_services() {
	$selected_method = $_GET['selected_method'];
	$services = \NotifyBot\Notifications\Services::get_instance()->get_depends( $selected_method );
	echo json_encode( $services );
	wp_die();
}

function nb_ajax_get_events() {
	$events = \NotifyBot\Notifications\Events::get_instance()->get_all();
	$events_formatted = array();
	foreach ( $events as $event ) {
		$events_formatted[] = array( 'id' => $event->id, 'label' => $event->label );
	}
	echo json_encode( $events_formatted );
	wp_die();
}

function nb_ajax_get_triggers() {
	$selected_trigger = $_GET['selected_event'];
	$triggers = \NotifyBot\Notifications\Triggers::get_instance()->get_depends( $selected_trigger );
	echo json_encode( $triggers );
	wp_die();
}

function nb_ajax_get_trigger_message() {

	$trigger_id  = $_GET['trigger_id'];
	$placeholder = $_GET['placeholder'];
	$merge_tags = \NotifyBot\Notifications\Triggers::get_instance()->get_merge_tags();
	$global_merge_tags = \NotifyBot\Global_Merge_Tags::$global_tags;

	error_log(print_r($merge_tags, true));

	$content = null;
	if ( ! empty( $placeholder ) ) {
		$content = $placeholder;
	}
	$merge_tags = array_merge($global_merge_tags, $merge_tags[ $trigger_id ]);
	$merge_tag_buttons = implode( ',', $merge_tags);

	$editor_id = 'notifybot-message-' . $trigger_id;
	$settings  = array(
		'textarea_rows' => 3,
		'tinymce'       => false,
		'media_buttons' => false,
		'textarea_name' => 'nb[trigger][' . $trigger_id . '][message]',
		'quicktags'     => array(
			'buttons' => $merge_tag_buttons
		)
	);

	error_log(print_r($merge_tags, true));

	ob_start();
	wp_editor( $content, $editor_id, $settings );
	$editortest = ob_get_clean();

	error_log(print_r($editortest, true));
	echo $editortest;

	\_WP_Editors::enqueue_scripts();
	print_footer_scripts();
	\_WP_Editors::editor_js();

	if ( wp_script_is('quicktags' ) ) { ?>

		<script type="text/javascript">
			<?php foreach ( $merge_tags as $tag ) : ?>
				QTags.addButton( '<?php echo $tag ?>', '<?php echo $tag ?>', '<?php echo '{' . $tag . '}' ?>', '', '', '<?php echo $tag ?>', '', 'notifybot-message-<?php echo $trigger_id ?>' );
			<?php endforeach; ?>
		</script>

	<?php }


	wp_die();
}

function nb_ajax_save_trigger() {

	$group_id = intval($_REQUEST['group_id']);

	if ( isset( $_REQUEST['conditional_logic_enabled'] ) ) {
		$conditional_logic_enabled = $_REQUEST['conditional_logic_enabled'];
	} else {
		$conditional_logic_enabled = false;
	}

	if ( isset( $_REQUEST['conditional_logic'] ) && isset( $_REQUEST['conditional_logic_enabled'] ) ) {
		$conditional_logic_rules = $_REQUEST['conditional_logic'];
	} else {
		$conditional_logic_rules = false;
	}

	$trigger_message           = $_REQUEST['message'];
	$trigger                   = $_REQUEST['trigger'];
	$trigger_id                = $_REQUEST['trigger_id'];

	$result = array(
		'success' => true,
	);

	$editing_existing_group = \NotifyBot\Models\Notifications::get_instance()->group_exists( $group_id );

	if ( $editing_existing_group ) {

		\NotifyBot\Models\Notifications::get_instance()->update_group( $group_id );

	} else {

		$group_insert = \NotifyBot\Models\Notifications::get_instance()->insert_group( true, null );

		if ( ! $group_insert['success'] ) {

			$result['success'] = false;
			$result['message'] = 'An error occurred with creating the new group.  Try adding a new notification or refreshing the page.';

			echo json_encode( $result );
			wp_die();
		}
	}

	// Check if the trigger already exists in the group
	$editing_existing_trigger = \NotifyBot\Models\Notifications::get_instance()->trigger_exists_in_group( $group_id, $trigger_id );

	// If the trigger already exists in the group
	if ( $editing_existing_trigger ) {

		$trigger_insert = \NotifyBot\Models\Notifications::get_instance()->update_trigger(
			array(
				'group_id'          => $group_id,
				'trigger_id'        => $trigger_id,
				'trigger_event'     => $trigger,
				'message'           => $trigger_message,
				'conditional_logic' => stripslashes($conditional_logic_rules)
			)
		);


		// If the trigger doesn't exist in the group
		} else {
			$trigger_insert = \NotifyBot\Models\Notifications::get_instance()->insert_trigger(
				array(
					'group_id'          => $group_id,
					'trigger_id'        => $trigger_id,
					'trigger_event'     => $trigger,
					'message'           => $trigger_message,
					'conditional_logic' => $conditional_logic_rules
				)
			);

			// If we don't get a success message, throw an error
			if ( ! $trigger_insert['success'] ) {

				$result['success'] = false;
				$result['message'] = 'An error occurred with creating the new trigger.  Check your fields or try refreshing the page';

			}
		}

	echo json_encode($result);
	wp_die();
}


function nb_ajax_get_existing() {

	$group_id = $_REQUEST['group_id'];

	if ( \NotifyBot\Models\Notifications::get_instance()->group_exists( $group_id ) ) {
		echo 'true';
	} else {
		echo 'false';
		wp_die();
	}
}