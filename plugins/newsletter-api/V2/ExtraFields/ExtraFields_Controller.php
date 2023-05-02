<?php

namespace TNP\API\V2\ExtraFields;

use TNP\API\V2\REST_Controller;
use TNP_Profile;
use TNP_Profile_Service;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class ExtraFields_Controller extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->rest_base = 'extrafields';
	}

	/**
	 * Registers the newsletters routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, "/$this->rest_base",
			[
				'schema' => [ $this, 'get_public_item_schema' ],
				// GET /extrafields
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
						'description' => __( 'Unique identifier for the extrafields.', 'newsletter' ),
						'type'        => 'integer',
						'required'    => true
					],
				],
				// GET /extrafields/#id
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
			'title'      => 'extrafield',
			'type'       => 'object',
			'properties' => [
				'id'       => [
					'description' => esc_html__( 'Unique identifier for extrafield.', 'newsletter' ),
					'type'        => 'integer',
					'readonly'    => true,
					'minimum'     => 1,
					'maximum'     => NEWSLETTER_PROFILE_MAX
				],
				'name'     => [
					'description' => esc_html__( 'Extrafield name.', 'newsletter' ),
					'type'        => 'string',
					'arg_options' => [
						'default' => '',
					],
				],
				'value'    => [
					'description' => esc_html__( 'Extrafield value.', 'newsletter' ),
					'type'        => 'string',
					'arg_options' => [
						'default' => '',
					],
				],
				'type'     => [
					'description' => esc_html__( 'Extrafield type.', 'newsletter' ),
					'type'        => 'string',
					'enum'        => [ 'text', 'select' ]
				],
				'options'  => [
					'description' => esc_html__( 'Extrafield options.', 'newsletter' ),
					'type'        => 'array',
					'items'       => [
						'type' => [ 'string', 'null' ],
					],
				],
				'required' => [
					'type' => 'boolean',
				]
			],
		);
	}

	public function get_items( $request ) {

		$extra_fields = TNP_Profile_Service::get_profiles();

		$extra_fields = array_values( array_map( function ( $extra_field ) use ( $request ) {
			return $this->prepare_item_for_response( $extra_field, $request );
		}, $extra_fields ) );

		return rest_ensure_response( $extra_fields );

	}

	public function get_item( $request ) {

		$profile_id = (int) $request->get_param( 'id' );
		$profile    = TNP_Profile_Service::get_profile_by_id( $profile_id );

		if ( is_null( $profile ) ) {
			return new WP_Error( '-1', __( 'Extra profile fields does not exist', 'newsletter' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $this->prepare_item_for_response( $profile, $request ) );
	}

	/**
	 * @param TNP_Profile $list
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $profile, $request ) {
		return [
			'id'       => (int) $profile->id,
			'name'     => $profile->name,
			'value'    => '',
			'options'  => array_values( $profile->options ),
			'type'     => $profile->type,
			'required' => $profile->rule == 1
		];
	}

}
