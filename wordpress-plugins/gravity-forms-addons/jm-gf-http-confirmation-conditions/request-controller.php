<?php
/**
 * Contains the JM_GF_HTTP_Confirmation_Conditions\Request_Controller class.
 *
 * @package JM_GF_HTTP_Confirmation_Conditions
 * @author  Jeff Matson <jeff@jeffmatson.net>
 */

namespace JM_GF_HTTP_Confirmation_Conditions;

class Request_Controller {

	public $url       = 'CHANGEME_SOME_HARDCODED_INTERNAL_API_URL';
	private $response = null;

	public function __construct( $args ) {
		$query_url = add_query_arg( $args, $this->url );
		$this->send_request( $query_url );
	}

	public function send_request( $url ) {
		$request       = wp_remote_get( $url );
		$response_body = wp_remote_retrieve_body( $request );

		if ( is_wp_error( $response_body ) ) {
			return false;
		}

		$this->response = json_decode( $response_body );

		return $this->response;
	}

	public function get_response() {
		return $this->response;
	}

	public function customer_exists() {
		return empty( ! $this->response->returnObject );
	}

}
