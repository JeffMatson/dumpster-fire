<?php
/**
 * Add Stripe Quantities
 *
 * @package     JM_GF_Stripe_Add_Quantity
 * @author      Jeff Matson
 * @copyright   2018 Jeff Matson
 * @license     GPL-3.0+
 *
 * @wordpress-plugin
 * Plugin Name: Gravity Forms Stripe Add Quantity
 * Plugin URI:  https://jeffmatson.net
 * Description: Adds quantities to Stripe data.
 * Version:     1.0.1
 * Author:      Jeff Matson
 * Author URI:  https://jeffmatson.net
 * Text Domain: jm-gf-stripe-add-quantity
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

add_filter( 'gform_stripe_charge_description', 'jm_gf_add_stripe_quantity', 10, 5 );

function jm_gf_add_stripe_quantity( $description, $strings, $entry, $submission_data, $feed ) {

	$description = '';

	foreach ( $submission_data['line_items'] as $line_item ) {
		$description .= $line_item['name'];

		if ( $line_item['quantity'] != '1' ) {
			$description .= ' (' . $line_item['quantity'] . ')';
		}

		if ( end( $submission_data['line_items'] ) !== $line_item ) {
			$description .= ', ';
		}
	}

	return $description;
}
