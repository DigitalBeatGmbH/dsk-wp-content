<?php

namespace TNP\API\V2\Newsletters;

use DateTime;
use Newsletter;
use TNP\API\V2\REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

class Newsletters_Controller extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->rest_base = 'newsletters';
	}

	/**
	 * Registers the newsletters routes
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, "/$this->rest_base",
			[
				'schema' => [ $this, 'get_public_item_schema' ],
				// GET /newsletters
				[
					'methods'             => WP_REST_Server::READABLE,
					'args'                => $this->get_collection_params(),
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => '__return_true',
				]
			]
		);

		register_rest_route( $this->namespace, "/$this->rest_base/(?P<id>[\d]+)",
			[
				'schema' => [ $this, 'get_public_item_schema' ],
				'args'   => [
					'id' => [
						'description' => __( 'Unique identifier for the subscriber.', 'newsletter' ),
						'type'        => 'integer',
						'required'    => true
					],
				],
				// GET /newsletters/#id
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'permissions_check' ]
				],
			]
		);

	}

	public function get_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'newsletter',
			'type'       => 'object',
			'properties' => array(
				'id'            => array(
					'description' => esc_html__( 'Unique identifier for newsletter.', 'newsletter' ),
					'type'        => 'integer',
					'readonly'    => true,
				),
				'subject'       => array(
					'description' => esc_html__( 'Newsletter\'s subject.', 'newsletter' ),
					'type'        => 'string',
					'arg_options' => array(
						'default' => '',
					),
				),
				'html_content'  => array(
					'description' => esc_html__( 'Newsletter HTML content.', 'newsletter' ),
					'type'        => 'string',
					'arg_options' => array(
						'default' => '',
					),
				),
				'plain_content' => array(
					'description' => esc_html__( 'Newsletter plain text content.', 'newsletter' ),
					'type'        => 'string',
					'arg_options' => array(
						'default' => '',
					),
				),
				'sent_on'       => array(
					'description' => esc_html__( 'Newsletter sent datetime.', 'newsletter' ),
					'type'        => 'string',
					'format'      => 'date_time'
				),
			),
		);
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();
		unset( $params['context'] );
		unset( $params['search'] );

		return $params;
	}

	public function get_item( $request ) {

		$id = $request->get_param( 'id' );

		$email = Newsletter::instance()->get_email( $id );

		$response = rest_ensure_response( $this->prepare_item_for_response( $email, $request ) );

		return $response;
	}

	public function get_items( $request ) {

		$show_only_public = true;
		if ( $this->is_authenticated_request() ) {
			$show_only_public = false;
		}

		$args = [
			'page'        => $request->get_param( 'page' ),
			'per_page'    => $request->get_param( 'per_page' ),
			'only_public' => $show_only_public,
			'type'        => apply_filters( 'newsletter_api_newsletters_type', [ 'message' ] )
		];

		$emails = $this->get_emails( $args );

		$emails = array_map( function ( $emails ) use ( $request ) {
			return $this->prepare_item_for_response( $emails, $request );
		}, $emails );

		$response = rest_ensure_response( $emails );

		return $response;
	}

	private function get_emails( $args, $format = OBJECT ) {
		global $wpdb;

		$default_args = array(
			'page'        => 1,
			'per_page'    => 10,
			'only_public' => true
		);

		$args = array_merge( $default_args, $args );

		$query      = "SELECT * FROM " . NEWSLETTER_EMAILS_TABLE . " WHERE status = 'sent' ";
		$query_args = [];

		$types_IN_clause_placeholder = implode( ',', array_fill( 0, count( $args['type'] ), '%s' ) );
		$query                       .= "AND type IN ($types_IN_clause_placeholder) ";
		$query_args                  = array_merge( $query_args, $args['type'] );

		if ( $args['only_public'] ) {
			$query .= "AND private = 0 ";
		}

		$query        .= "ORDER BY id DESC ";
		$query        .= "LIMIT %d OFFSET %d";
		$query_args[] = (int) $args['per_page'];
		$query_args[] = ( (int) $args['page'] - 1 ) * (int) $args['per_page'];

		$records = $wpdb->get_results( $wpdb->prepare( $query, $query_args ), $format );

		if ( $wpdb->last_error ) {
			return null;
		}

		return $records;
	}

	/**
	 * Prepares a single subscriber output for response.
	 *
	 * @param  $newsletter
	 * @param WP_REST_Request $request Request object.
	 *
	 */
	public function prepare_item_for_response( $newsletter, $request ) {

		$sent_on = ( new DateTime() )->setTimestamp( (int) $newsletter->send_on );

		$data = array(
			'id'            => (int) $newsletter->id,
			'subject'       => $newsletter->subject,
			'html_content'  => $newsletter->message,
			'plain_content' => is_null( $newsletter->message_text ) ? '' : $newsletter->message_text,
			'sent_on'       => $sent_on->format( DateTime::ATOM ),
		);

		return $data;

	}

}
