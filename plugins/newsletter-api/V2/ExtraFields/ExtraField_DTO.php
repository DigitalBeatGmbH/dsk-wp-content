<?php

namespace TNP\API\V2\ExtraFields;

class ExtraField_DTO {
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $value;
	/**
	 * @var string[]
	 */
	public $options;
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @var bool
	 */
	public $required;

	/**
	 * @var string
	 */
	public $visibility;


}
