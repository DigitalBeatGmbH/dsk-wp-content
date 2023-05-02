<?php

namespace TNP\Webhooks;

class Webhook {
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $url;
	/**
	 * @var string
	 */
	public $trigger;

	public $description;

	public $http_verb;

	/**
	 * Webhook constructor.
	 *
	 * @param $url string
	 * @param $trigger string
	 */
	public function __construct( $id, $url, $trigger, $description = '', $http_verb = 'POST' ) {
		$this->id          = $id;
		$this->url         = $url;
		$this->trigger     = $trigger;
		$this->description = $description;
		$this->http_verb   = $http_verb;
	}

}
