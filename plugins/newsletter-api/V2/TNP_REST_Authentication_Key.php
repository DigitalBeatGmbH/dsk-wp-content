<?php

namespace TNP\API\V2;

use WP_User;

/**
 * REST API authentication key model.
 */
class TNP_REST_Authentication_Key {

	/* @var int */
	private $id;
	/* @var int */
	private $user_id;
	/* @var string */
	private $client_key;
	/* @var string */
	private $client_secret;
	/* @var string */
	private $description;
	/* @var WP_User */
	private $user;
	/* @var string */
	private $permissions;

	/**
	 * TNP_REST_Authentication_Key constructor.
	 *
	 * @param int $id
	 * @param int $user_id
	 * @param string $client_key
	 * @param string $client_secret
	 * @param string $permissions
	 * @param string $description
	 */
	public function __construct( $id, $user_id, $client_key, $client_secret, $permissions, $description = '' ) {
		$this->id            = $id;
		$this->user_id       = $user_id;
		$this->client_key    = $client_key;
		$this->client_secret = $client_secret;
		$this->permissions   = $permissions;
		$this->description   = $description;
		$this->user          = null;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * @return WP_User
	 */
	public function get_user() {
		if ( is_null( $this->user ) ) {
			$user       = get_user_by( 'id', $this->user_id );
			$this->user = $user instanceof WP_User ? $user : null;
		}

		return $this->user;
	}

	/**
	 * @return string
	 */
	public function get_client_key() {
		return $this->client_key;
	}

	/**
	 * @return string
	 */
	public function get_client_secret() {
		return $this->client_secret;
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function get_permissions() {
		return $this->permissions;
	}

}
