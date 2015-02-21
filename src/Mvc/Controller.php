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

		$this->init();
	}

	protected function _loadComponents()
	{
		$config = $this->application->getConfig()->read('components');
		$this->components = new Storage();
		$args = [
			'application' => $this->application,
			'controller' => $this,
		];
		foreach ($config as $key => $component) {
			$this->components->write(
				$key, 
				(is_callable($component)) ? 
					call_user_func_array($component, [$this->application, $this]) : 
					(
						(is_object($component)) ? 
							$component :
							new $component($this->application, $this)
					)
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

	public function init()
	{

	}
	
}