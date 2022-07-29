<?php
/**
 * Contains the GF_Entry_Approval\Merge_Tags class.
 *
 * @package GF_Entry_Approval
 */

namespace GF_Entry_Approval;

/**
 * Processes custom merge tags.
 *
 * @package GF_Entry_Approval
 */
class Merge_Tags {

	/**
	 * Generates the entry approval URL.
	 *
	 * @param string $text       The current content to be searched.
	 * @param array  $form       The Form Object.
	 * @param array  $entry      The Entry Object.
	 * @param bool   $url_encode If the URL is encoded.
	 * @param bool   $esc_html   If HTML will be escaped.
	 * @param bool   $nl2br      If nl2br will be run.
	 * @param string $format     The current format.
	 *
	 * @return string
	 */
	public function entry_approval_url( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		// Define the merge tag to process.
		$custom_merge_tag = '{entry_approval_url}';

		// Ensure the merge tag actually exists in the content.
		if ( strpos( $text, $custom_merge_tag ) === false ) {
			return $text;
		}

		// Gets the approval ID for the entry.
		$approval_id  = gform_get_meta( $entry['id'], 'approval_key' );
		// Builds the URL for approval.
		$approval_url = trailingslashit( $entry['source_url'] ) . '?approval_id=' . $approval_id;

		// Replace the tag with the URL.
		$text = str_replace( $custom_merge_tag, $approval_url, $text );

		return $text;
	}
}
