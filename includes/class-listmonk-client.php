<?php
/**
 * Listmonk API client helper.
 *
 * @package EightAM\GravityFormsListmonk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lightweight HTTP client for Listmonk.
 */
class EAGF_Listmonk_Client {
	/**
	 * Base Listmonk URL.
	 *
	 * @var string
	 */
	protected $base_url;

	/**
	 * API username.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * API key/token.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * Constructor.
	 *
	 * @param string $base_url Base URL.
	 * @param string $username Username.
	 * @param string $api_key  API key/token.
	 */
	public function __construct( $base_url, $username, $api_key ) {
		$this->base_url = untrailingslashit( trim( $base_url ) );
		$this->username = $username;
		$this->api_key  = $api_key;
	}

	/**
	 * Fetch all lists (up to 200).
	 *
	 * @return array{success:bool,message:string,data:array|null}
	 */
	public function get_lists() {
		$query = add_query_arg(
			array(
				'page'     => 1,
				'per_page' => 200,
			),
			'lists'
		);

		return $this->request( 'GET', $query );
	}

	/**
	 * Find subscriber by email.
	 *
	 * @param string $email Email.
	 * @return array{success:bool,message:string,data:array|null}
	 */
	public function find_subscriber_by_email( $email ) {
		// Debug: Log the email being looked up
		error_log( 'Listmonk: Looking up email: ' . $email );
		
		// Escape single quotes for Listmonk SQL query
		$escaped_email = str_replace( "'", "''", $email );
		$query = sprintf( "subscribers.email = '%s'", $escaped_email );
		
		error_log( 'Listmonk: SQL query before URL encoding: ' . $query );
		
		// Build the path manually to avoid add_query_arg URL encoding issues with special chars
		$path = sprintf(
			'subscribers?page=1&per_page=1&query=%s',
			rawurlencode( $query )
		);
		
		error_log( 'Listmonk: Final API path: ' . $path );

		return $this->request( 'GET', $path );
	}

	/**
	 * Create subscriber.
	 *
	 * @param array $payload Request payload.
	 * @return array
	 */
	public function create_subscriber( $payload ) {
		return $this->request( 'POST', 'subscribers', array( 'body' => wp_json_encode( $payload ) ) );
	}

	/**
	 * Update subscriber.
	 *
	 * @param int   $subscriber_id ID.
	 * @param array $payload       Payload.
	 * @return array
	 */
	public function update_subscriber( $subscriber_id, $payload ) {
		return $this->request( 'PUT', sprintf( 'subscribers/%d', (int) $subscriber_id ), array( 'body' => wp_json_encode( $payload ) ) );
	}

	/**
	 * Upsert: create or update subscriber by email.
	 * When updating, merges new data with existing data instead of replacing.
	 *
	 * @param array $payload Payload.
	 * @return array
	 */
	public function upsert_subscriber( $payload ) {
		$response = $this->create_subscriber( $payload );

		if ( $response['success'] ) {
			return $response;
		}

		// Debug: Log the create response
		error_log( 'Listmonk create response: ' . print_r( $response, true ) );

		if ( 409 === $response['code'] ) {
			error_log( 'Listmonk: Subscriber exists (409), attempting lookup for: ' . rgar( $payload, 'email' ) );
			
			$existing = $this->find_subscriber_by_email( rgar( $payload, 'email' ) );
			
			// Debug: Log the lookup response
			error_log( 'Listmonk lookup response: ' . print_r( $existing, true ) );

			if ( $existing['success'] && ! empty( $existing['data']['data']['results'][0] ) ) {
				$subscriber_id = (int) $existing['data']['data']['results'][0]['id'];
				$existing_data = $existing['data']['data']['results'][0];
				
				error_log( 'Listmonk: Found existing subscriber ID: ' . $subscriber_id );
				
				// Merge the payload with existing data
				$merged_payload = $this->merge_subscriber_data( $existing_data, $payload );
				
				error_log( 'Listmonk merged payload: ' . print_r( $merged_payload, true ) );
				
				return $this->update_subscriber( $subscriber_id, $merged_payload );
			}
			
			// If lookup failed, return error with more context
			error_log( 'Listmonk: Lookup failed or no results found' );
			return array(
				'success' => false,
				'message' => sprintf( 
					'Subscriber exists but lookup failed: %s', 
					$existing['success'] ? 'No results found' : $existing['message'] 
				),
				'data'    => $existing['data'],
				'code'    => 409,
			);
		}

		return $response;
	}

	/**
	 * Merge new subscriber data with existing data.
	 *
	 * @param array $existing Existing subscriber data from Listmonk.
	 * @param array $new      New payload to merge.
	 * @return array Merged payload.
	 */
	protected function merge_subscriber_data( $existing, $new ) {
		$merged = $new;

		// Merge lists: combine existing and new lists, remove duplicates
		$existing_lists = isset( $existing['lists'] ) && is_array( $existing['lists'] ) 
			? array_column( $existing['lists'], 'id' ) 
			: array();
		$new_lists = isset( $new['lists'] ) && is_array( $new['lists'] ) 
			? $new['lists'] 
			: array();
		
		$all_lists = array_unique( array_merge( $existing_lists, $new_lists ) );
		$merged['lists'] = array_values( array_map( 'intval', $all_lists ) );

		// Merge attributes: new values override old, but keep old values not in new
		$existing_attribs = isset( $existing['attribs'] ) && is_object( $existing['attribs'] ) 
			? (array) $existing['attribs'] 
			: array();
		$new_attribs = isset( $new['attribs'] ) && is_object( $new['attribs'] ) 
			? (array) $new['attribs'] 
			: array();
		
		$merged_attribs = array_merge( $existing_attribs, $new_attribs );
		$merged['attribs'] = (object) $merged_attribs;

		// Keep existing name if new name is empty
		if ( empty( $merged['name'] ) && ! empty( $existing['name'] ) ) {
			$merged['name'] = $existing['name'];
		}

		// Preserve existing status unless explicitly changing
		if ( ! isset( $merged['status'] ) && isset( $existing['status'] ) ) {
			$merged['status'] = $existing['status'];
		}

		return $merged;
	}

	/**
	 * Perform HTTP request.
	 *
	 * @param string $method HTTP method.
	 * @param string $path   API path relative to /api/.
	 * @param array  $args   Additional args.
	 * @return array{success:bool,message:string,data:array|null,code:int}
	 */
	protected function request( $method, $path, $args = array() ) {
		$url = trailingslashit( $this->base_url ) . 'api/' . ltrim( $path, '/' );

		$defaults = array(
			'method'  => $method,
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->api_key ),
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			),
		);

		$args      = wp_parse_args( $args, $defaults );
		$response  = wp_remote_request( $url, $args );
		$code      = wp_remote_retrieve_response_code( $response );
		$body      = wp_remote_retrieve_body( $response );
		$decoded   = json_decode( $body, true );
		$wp_error  = is_wp_error( $response ) ? $response : null;
		$success   = ! $wp_error && $code >= 200 && $code < 300;
		$message   = $success ? __( 'OK', 'eightam-gf-listmonk' ) : $this->build_error_message( $wp_error, $decoded, $code );

		return array(
			'success' => $success,
			'message' => $message,
			'data'    => $decoded,
			'code'    => $code,
		);
	}

	/**
	 * Create friendly error message.
	 *
	 * @param WP_Error|null $wp_error WP error.
	 * @param array|null    $decoded  Response body.
	 * @param int           $code     HTTP code.
	 * @return string
	 */
	protected function build_error_message( $wp_error, $decoded, $code ) {
		if ( $wp_error instanceof WP_Error ) {
			return $wp_error->get_error_message();
		}

		if ( isset( $decoded['message'] ) ) {
			return sprintf( '%s (HTTP %d)', $decoded['message'], $code );
		}

		return sprintf( __( 'Unexpected Listmonk response (HTTP %d)', 'eightam-gf-listmonk' ), $code );
	}
}
