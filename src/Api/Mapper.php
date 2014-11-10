<?php

namespace Barbare\Framework\Api;

class Mapper
{

	protected $maker;
	protected $collections = [];

	protected static $methods = [
		'findAll',
		'findBy',
		'findOne'
	];

	public function __construct($maker)
	{
		$this->maker = $maker;
	}

	public function __call($name, $args = [])
	{
		// Methods created by ApiMaker
		if(in_array($name, array_keys($this->maker->getRessource()->getMapperMethods()))) {
			return call_user_func_array($this->maker->getRessource()->getMapperMethod($name), array_merge([$this], $args));
		}
		// Methods by default mapper
		if(in_array($name, self::$methods)) {
			return call_user_func_array([$this, $name], $args);
		}
		// others methods in the mapper class
		if(method_exists($this, $name)) {
			return call_user_func_array([$this, $name], $args);
		}
		return false;
	}


}