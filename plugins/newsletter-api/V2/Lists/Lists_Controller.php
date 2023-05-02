<?php

namespace TNP\API\V2\Lists;

use NewsletterSubscription;
use TNP\API\V2\REST_Controller;
use TNP_List;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Lists_Controller extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->rest_base = 'lists';
	}

	/**
	 * Registers the newsletters routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, "/$this->rest_base",
			[
				'schema' => [ $this, 'get_public_item_schema' ],
				// GET /lists
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'permissions_check' ]
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
				// GET /lists/#id
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'permissions_check' ]
				]
			]
		);

	}

	public function get_item_schema() {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'list',
			'type'       => 'object',
			'properties' => [
				'id'   => [
					'description' => esc_html__( 'Unique identifier for list.', 'newsletter' ),
					'type'        => 'integer',
					'readonly'    => true,
					'minimum'     => 1,
					'maximum'     => NEWSLETTER_LIST_MAX
				],
				'name' => [
					'description' => esc_html__( 'List\'s name.', 'newsletter' ),
					'type'        => 'string',
					'arg_options' => [ 'default' => '' ],
				],
				'type' => [
					'description' => esc_html__( 'List type.', 'newsletter' ),
					'type'        => 'string',
					'enum'        => [ 'public', 'private' ]
				],
			],
		];
	}

	public function get_items( $request ) {

		$lists = NewsletterSubscription::instance()->get_lists();

		$lists = array_values( array_map( function ( $list ) use ( $request ) {
			return $this->prepare_item_for_response( $list, $request );
		}, $lists ) );

		return rest_ensure_response( $lists );

	}

	public function get_item( $request ) {
		$list = NewsletterSubscription::instance()->get_list( (int) $request->get_param( 'id' ) );

		if ( is_null( $list ) ) {
			return new WP_Error( '-1', __( 'List does not exist', 'newsletter' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $list, $request ) );
	}

	/**
	 * @param TNP_List $list
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $list, $request ) {
		return [
			'id'   => (int) $list->id,
			'name' => $list->name
		];
	}

}
