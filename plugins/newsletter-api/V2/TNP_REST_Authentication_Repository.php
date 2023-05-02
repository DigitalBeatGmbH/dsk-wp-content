<?php

namespace TNP\API\V2;

use stdClass;

/**
 * REST API authentication repository class.
 */
class TNP_REST_Authentication_Repository {

	private $table_name;
	private $db;

	public static function getInstance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	protected function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = $this->db->prefix . 'newsletter_api_keys';
	}

	public function create_table() {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $this->db->get_charset_collate();

		$table = "CREATE TABLE {$this->table_name} (
				  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				  user_id BIGINT UNSIGNED NOT NULL,
				  description VARCHAR(200) NULL,
				  client_key CHAR(64) NOT NULL,
				  client_secret CHAR(43) NOT NULL,
				  permissions VARCHAR(10) NOT NULL,			  
				  PRIMARY KEY  (id),
				  KEY client_key (client_key),
				  KEY client_secret (client_secret)
				) $charset_collate";

		dbDelta( $table );

	}

	/**
	 * @param stdClass $obj
	 */
	private function from_db( $obj ) {
		return new TNP_REST_Authentication_Key(
			(int) $obj->id,
			(int) $obj->user_id,
			$obj->client_key,
			$obj->client_secret,
			$obj->permissions,
			$obj->description
		);
	}

	/**
	 * @return TNP_REST_Authentication_Key[]
	 */
	public function all() {

		$query = "SELECT * FROM {$this->table_name}";

		$records = $this->db->get_results( $query );

		if ( ! empty( $records ) ) {
			return array_map( array( $this, 'from_db' ), $records );
		}

		return [];

	}

	/**
	 * @param $client_key
	 *
	 * @return TNP_REST_Authentication_Key|null
	 */
	public function get_authentication_key_by_client_key( $client_key ) {

		$query = "SELECT * FROM {$this->table_name} WHERE client_key = %s";

		$record = $this->db->get_row( $this->db->prepare( $query, $client_key ) );

		if ( ! empty( $record ) ) {
			return $this->from_db( $record );
		}

		return null;

	}


	/**
	 * @param TNP_REST_Authentication_Key $key
	 *
	 * @return int|false
	 */
	public function insert( $key ) {

		$ret = $this->db->insert( $this->table_name,
			[
				'user_id'       => (int) $key->get_user_id(),
				'description'   => sanitize_text_field( $key->get_description() ),
				'client_key'    => $key->get_client_key(),
				'client_secret' => $key->get_client_secret(),
				'permissions'   => $key->get_permissions()
			]
		);

		return $ret > 0 ? $this->db->insert_id : false;

	}

	/**
	 * @param $key_id
	 *
	 * @return bool
	 */
	public function delete( $key_id ) {

		$ret = $this->db->delete( $this->table_name,
			[
				'id' => (int) $key_id
			]
		);

		return $ret > 0;
	}

}
