<?php
/**
 * Contains the GF_Entry_Approval\Approval class.
 *
 * @package GF_Entry_Approval
 */

namespace GF_Entry_Approval;

/**
 * Handles functionality related to the entry approval process.
 *
 * @package GF_Entry_Approval
 */
class Approval {

	/**
	 * Displays the approval prompt, based on current query strings.
	 *
	 * @param string $form_string HTML markup for the current form.
	 * @param array  $form        The Form Object.
	 *
	 * @return string
	 */
	public function maybe_display_approval_prompt( $form_string, $form ) {
		$approval_id = rgget( 'approval_id' );
		$approval_status = rgget( 'approval_status' );

		// If there isn't an approval ID in the query string, display the form normally.
		if ( ! $approval_id ) {
			return $form_string;
		}

		$entry = $this->get_entry_by_approval_key( $form['id'], $approval_id );

		// If an entry is found matching the approval ID.
		if ( ! is_wp_error( $entry ) ) {

			// Check if the approval status has been set in the query string.
			if ( $approval_status == ( 'approved' || 'denied' ) ) {
				// Update the approval status.
				$this->update_approval_status( $entry );
				// Trigger notification events.
				$this->trigger_notification( $form, $entry, $approval_status );

				// Display the approval status.
				return $this->display_approval_status( $entry );
			}

			// Generate the entry details.
			$form_string = $this->display_entry_details( $form['fields'], $entry );

			// Build the approval prompt form.
			$form_string .= $this->display_approval_prompt( $entry );
		} else {
			// Give some info if the ID passed in the query string is invalid.
			return '<p>Invalid ID</p>';
		}

		// Pass our new markup.
		return $form_string;
	}

	/**
	 * Displays the entry details within the approval page.
	 *
	 * @param array $fields All fields present on the current form.
	 * @param array $entry  The Entry Object being processed.
	 *
	 * @return string The entry details markup.
	 */
	public function display_entry_details( $fields, $entry ) {
		// Define our valiable to build off of.
		$form_string = '';

		// Run through each field in the form.
		foreach ( $fields as $field ) {

			// Check if the field is a checkbox, radio, etc with multiple inputs.
			if ( is_array( $field['inputs'] ) ) {
				// Run through each input.
				foreach ( $field['inputs'] as $input ) {
					// Get the entry value for the input.
					$entry_value = $entry[ strval( $input['id'] ) ];

					// Make sure the value isn't empty and set the markup.
					if ( ! empty( $entry_value ) ) {
						$form_string .= '<p><strong>' . $input['label'] . ': </strong>' . $entry_value . '</p>';
					}
				}
			} else {
				// Get the entry value.
				$entry_value = $entry[ $field['id'] ];

				// Make sure the value isn't empty and set the markup.
				if ( ! empty( $entry_value ) ) {
					$form_string .= '<p><strong>' . $field['label'] . ': </strong>' . $entry_value . '</p>';
				}
			}
		}

		return $form_string;
	}

	/**
	 * Gets the related entry based on the passed approval ID.
	 *
	 * @param int    $form_id     The form ID.
	 * @param string $approval_id The approval ID to search for.
	 *
	 * @return array|WP_Error
	 */
	public function get_entry_by_approval_key( $form_id, $approval_id ) {
		// Get entries matching the approval ID.
		$entries = \GFAPI::get_entries( $form_id, array(
			'field_filters' => array(
				array(
					'key' => 'approval_key',
					'value' => $approval_id,
				),
			),
		) );

		// If no entry is found, pass on the error.
		if ( is_wp_error( $entries ) ) {
			return $entries;
		}

		return $entries[0];
	}

	/**
	 * Triggers a notification to be sent based on the approval status.
	 *
	 * @param array  $form            The Form Object.
	 * @param array  $entry           The Entry Object.
	 * @param string $approval_status The current approval status.
	 *
	 * @return void
	 */
	public function trigger_notification( $form, $entry, $approval_status ) {
		if ( $approval_status == 'approved' ) {
			\GFAPI::send_notifications( $form, $entry, 'entry_approved' );
		} elseif ( $approval_status == 'denied' ) {
			\GFAPI::send_notifications( $form, $entry, 'entry_denied' );
		}
	}

	/**
	 * Displays the approval prompt markup.
	 *
	 * @return string
	 */
	public function display_approval_prompt() {
		$approval_form  = '<form>';
		$approval_form .= '<input type="radio" name="approval_status" value="approved"> Approved<br>';
		$approval_form .= '<input type="radio" name="approval_status" value="denied"> Denied<br>';
		$approval_form .= '<input type="hidden" name="approval_id" value="' . rgget( 'approval_id' ) . '">';
		$approval_form .= '<input type="submit" value="Submit">';
		$approval_form .= '</form>';

		return $approval_form;
	}

	/**
	 * Updates the approval status.
	 *
	 * @param array $entry The Entry Object.
	 *
	 * @return void
	 */
	public function update_approval_status( $entry ) {
		$approval_status = rgget( 'approval_status' );

		if ( $approval_status == ( 'approved' || 'denied' ) ) {
			gform_update_meta( $entry['id'], 'approval_status', $approval_status );
		}
	}

	/**
	 * Displays the current approval status.
	 *
	 * @param array $entry The Entry Object.
	 *
	 * @return string
	 */
	public function display_approval_status( $entry ) {
		return '<p>' . gform_get_meta( $entry['id'], 'approval_status' ) . '</p>';
	}
}
