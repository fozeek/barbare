<?php

namespace Barbare\Framework\Router;

use Barbare\Framework\Event\Event;

class Route
{

	protected $controller;
	protected $action;
	protected $params;

	public function __construct($controller, $action, $params)
	{
		$this->controller = $controller;
		$this->action = $action;
		$this->params = $params;
	}

	public function toArray($withParams = true)
	{
		$route = [
			'controller' => $this->controller,
			'action' => $this->action
		];

		if($withParams) {
			return array_merge(
				$route,
				['params' => $this->params]
			);
		}
		return $route;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function getParams()
	{
		return $this->params;
	}

	public function getParam($key)
	{
		return $this->params[$key];
	}

}