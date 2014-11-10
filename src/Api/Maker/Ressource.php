<?php

namespace Barbare\Framework\Api\Maker;

class Ressource
{

	protected $name;
	protected $tableName;
	protected $defaultOrder;
	protected $canBeUpdated;
	protected $attributs = [];
	protected $methods = [];
	protected $mapperMethods = [];

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getMapperMethods()
	{
		return $this->mapperMethods;
	}

	public function getMapperMethod($name)
	{
		return $this->mapperMethods[$name];
	}

	public function tableName($tableName)
	{
		$this->tableName = $tableName;
		return $this;
	}

	public function defaultOrder($attribut, $order)
	{
		$this->defaultOrder = [$attribut, $order];
		return $this;
	}

	public function canBeUpdated($canBeUpdated)
	{
		$this->canBeUpdated = $canBeUpdated;
		return $this;
	}

	public function addAttribut($name, $callback)
	{
		$attribut = new Attribut($name);
		$callback($attribut);
		$this->attributs[] = $attribut;
		return $this;
	}

	public function addMethod($name, $method)
	{
		$this->methods[$name] = $method;
		return $this;
	}

	public function addMapperMethod($name, $method)
	{
		$this->mapperMethods[$name] = $method;
		return $this;
	}
}