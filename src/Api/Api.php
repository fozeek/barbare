<?php

namespace Barbare\Framework\Api;

use Barbare\Framework\Api\Maker\Maker;
use Barbare\Framework\Api\Mapper;

class Api
{
	protected $ressources = [];

	public function maker()
	{
		$ressource = new Maker();
		$this->ressources[] = $ressource;
		return $ressource;
	}

	public function get($name)
	{
		foreach ($this->ressources as $ressource) {
			if($ressource->getRessource()->getName() == $name) {
				return $this->_mapper($ressource);
			}
		}
	}

	protected function _mapper($ressource)
	{
		return new Mapper($ressource);
	}
}