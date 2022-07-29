<?php
/**
 * Contains the GF_Entry_Approval\Notifications class.
 *
 * Handles the addition of entry approval notification events.
 *
 * @package GF_Entry_Approval
 */

namespace GF_Entry_Approval;

/**
 * Main notifications class.
 *
 * @package GF_Entry_Approval
 */
class Notifications {

	/**
	 * Adds additional notification options.
	 *
	 * @param array $notification_events The currently registered notification events.
	 */
	public function send_on_approval( $notification_events ) {
		$notification_events['entry_approved'] = 'Entry is approved';
		$notification_events['entry_denied']   = 'Entry is denied';
		$notification_events['entry_pending']  = 'Entry is pending';

		return $notification_events;
	}
}
