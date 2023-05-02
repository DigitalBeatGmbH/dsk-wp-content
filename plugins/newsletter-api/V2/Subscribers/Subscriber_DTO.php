<?php

namespace TNP\API\V2\Subscribers;

class Subscriber_DTO {
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $email;
	/**
	 * @var string
	 */
	public $first_name;
	/**
	 * @var string
	 */
	public $last_name;
	/**
	 * @var string
	 */
	public $gender;
	/**
	 * @var string
	 */
	public $country;
	/**
	 * @var string
	 */
	public $region;
	/**
	 * @var string
	 */
	public $city;

	public $lists;

	public $extra_fields;
	/**
	 * @var string
	 */
	public $status;


}
