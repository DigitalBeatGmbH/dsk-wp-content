<?php

namespace TNP\API\V2\Lists;

use NewsletterSubscription;
use TNP_List;

class List_Repository {

	public static function instance() {
		static $instance = null;
		if ( null == $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	protected function __construct() {
	}

	/**
	 * @return TNP_List[]
	 */
	private function all() {
		$current_language = NewsletterSubscription::instance()->get_current_language();
		$db_lists_array   = NewsletterSubscription::instance()->get_options( 'lists', $current_language );

		$tnp_lists = [];

		for ( $i = 1; $i <= NEWSLETTER_LIST_MAX; $i ++ ) {
			if ( empty( $db_lists_array[ 'list_' . $i ] ) ) {
				continue;
			}
			$tnp_list = NewsletterSubscription::instance()->create_tnp_list_from_db_lists_array( $db_lists_array, $i );

			$tnp_lists[ (string) $tnp_list->id ] = $tnp_list;
		}

		return $tnp_lists;
	}

	/**
	 * @param int $list_id
	 *
	 * @return TNP_List|null
	 */
	public function get_by_id( $list_id ) {

		$lists = $this->all();
		foreach ( $lists as $list ) {
			if ( $list->id == $list_id ) {
				return $list;
			}
		}

		return null;

	}

	/**
	 * @param TNP_List $list
	 *
	 * @return true
	 * @throws AlreadyExistsList
	 */
	public function add( $list ) {

		if ( $this->exists( $list ) ) {
			throw new AlreadyExistsList();
		}

		$current_language = NewsletterSubscription::instance()->get_current_language();

		$lists_db = $this->all();
		$lists_db = array_merge( $lists_db, $this->tnp_list_to_db( $list ) );

		NewsletterSubscription::instance()->save_options( $lists_db, 'lists', null, $current_language );

		return true;

	}

	/**
	 * @param TNP_List $list
	 */
	public function exists( $list ) {
		return ! is_null( $this->get_by_id( $list->id ) );
	}

	/**
	 * @param TNP_List $tnp_list
	 */
	private function tnp_list_to_db( $tnp_list ) {

		$lists_db["list_$tnp_list->id"]          = $tnp_list->name;
		$lists_db["list_{$tnp_list->id}_status"] = $tnp_list->status;

		/*		$lists_db["list_{$list->id}_forced"]       = $list->forced;
				$lists_db["list_{$list->id}_subscription"] = $list->show_on_subscription ? TNP_List::SUBSCRIPTION_SHOW : TNP_List::SUBSCRIPTION_HIDE;
				$lists_db["list_{$list->id}_profile"]      = $list->show_on_profile ? TNP_List::PROFILE_SHOW : TNP_List::PROFILE_HIDE;*/

		return $lists_db;

	}

}
