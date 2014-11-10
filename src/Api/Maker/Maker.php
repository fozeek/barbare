<?php

namespace Barbare\Framework\Api\Maker;

class Maker
{

	protected $ressource;

	public function add($name, $callback) {
		$callback($this->ressource = new Ressource($name));
	}

	public function getRessource() {
		return $this->ressource;
	}
}