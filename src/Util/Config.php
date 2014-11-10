<?php

namespace Barbare\Framework\Util;

use Barbare\Framework\Mvc\Component;

class Config extends Component
{

	protected $config;

	public function __construct($application, $values)
	{
		$this->config = new Storage($application->getConfig()->read('config'));
	}

	public function get($key) {
		return $this->config->read($key);
	}

}