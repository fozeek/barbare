<?php

namespace Barbare\Framework\Mvc;

use Barbare\Framework\Util\Storage;

class Controller
{

	protected $components;
	protected $application;

	public function __construct(Application $application)
	{
		$this->application = $application;
		$this->_loadComponents();
	}

	protected function _loadComponents()
	{
		$config = $this->application->getConfig()->read('components');
		$this->components = new Storage();
		$args = [
			'application' => $this->application,
			'controller' => $this,
		];
		foreach ($config as $key => $value) {
			$this->components->write(
				$key, 
				(is_callable($value)) ? call_user_func_array($value, [$this->application, $args]) : new $value($this->application, $args)
			);
		}
	}

	public function __get($attribut)
	{
		if($attribut[0] != '_') {
			return $this->components->read(strtolower($attribut));
		} else {
			return $this->attribut;
		}
	}
	
}